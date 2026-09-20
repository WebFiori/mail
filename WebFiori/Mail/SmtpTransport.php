<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Mail;

use WebFiori\Mail\Exceptions\SMTPException;

/**
 * SMTP transport implementation that delivers emails via SMTP protocol.
 *
 * This is the default transport used by the library. It extracts the SMTP
 * delivery logic that was previously embedded in Email::send().
 *
 * @author Ibrahim
 */
class SmtpTransport implements TransportInterface {
    private SMTPAccount $account;
    private ?SMTPServer $server;

    /**
     * Creates a new SMTP transport instance.
     *
     * @param SMTPAccount $account The SMTP account to use for authentication.
     *
     * @param SMTPServer|null $server An optional pre-connected server instance.
     * If null, a new connection will be established using the account details.
     */
    public function __construct(SMTPAccount $account, ?SMTPServer $server = null) {
        $this->account = $account;
        $this->server = $server;
    }

    /**
     * Returns the SMTP account used by this transport.
     *
     * @return SMTPAccount
     */
    public function getAccount(): SMTPAccount {
        return $this->account;
    }

    public function getName(): string {
        return 'smtp';
    }

    /**
     * Returns the SMTP server instance used by this transport.
     *
     * @return SMTPServer
     */
    public function getServer(): SMTPServer {
        if ($this->server === null) {
            $this->server = new SMTPServer(
                $this->account->getServerAddress(),
                $this->account->getPort(),
                $this->account->isVerifySsl(),
                $this->account->isAllowSelfSigned(),
                $this->account->getMaxRetries(),
                $this->account->getRetryBaseDelay()
            );
        }

        return $this->server;
    }

    /**
     * Send an email message via SMTP.
     *
     * @param Email $message The email to send.
     *
     * @throws SMTPException If authentication or sending fails.
     */
    public function send(Email $message): void {
        $acc = $this->account;
        $server = $this->getServer();

        if ($message->rcptCount() == 0) {
            throw new SMTPException('No message recipients.');
        }

        $isExternal = $this->server !== null && $server->isConnected();

        if ($isExternal || $this->authenticate($server, $acc)) {
            $server->sendCommand('MAIL FROM: <'.$acc->getAddress().'>');

            $accepted = $this->sendRecipients($message, $server);

            if ($accepted === 0) {
                // All recipients were rejected. Capture the rejection details
                // before clearing the error state, then close the connection
                // cleanly (clearErrorState so QUIT itself is not blocked by the
                // guard) before failing so the socket is not left dangling.
                $lastResponse = $server->getLastResponse();
                $lastCode = $server->getLastResponseCode();
                $server->clearErrorState();
                $server->sendCommand('QUIT');

                throw new SMTPException(
                    'All recipients were rejected by the SMTP server. '
                        .'Last response: '.$lastResponse,
                    $lastCode,
                    $server->getLog()
                );
            }

            // At least one recipient accepted. Clear any residual rejection
            // state (from a trailing rejected recipient) so DATA is not blocked.
            $server->clearErrorState();
            $server->sendCommand('DATA');
            $this->sendHeaders($message, $server, $acc);
            $this->sendBody($message, $server);
            $this->sendAttachments($message, $server);
            $server->sendCommand(SMTPServer::NL.'.');
            $server->sendCommand('QUIT');
        } else {
            throw new SMTPException(
                'Unable to login to SMTP server: '.$server->getLastResponse(),
                $server->getLastResponseCode(),
                $server->getLog()
            );
        }
    }

    private function authenticate(SMTPServer $server, SMTPAccount $account): bool {
        // Token provider takes highest precedence — token is fetched lazily here,
        // just before authentication, ensuring it is always fresh.
        $provider = $account->getTokenProvider();

        if ($provider !== null) {
            $token = $provider->getToken();

            return $server->authOAuth($account->getUsername(), $token);
        }

        // Fall back to static access token (backward compatibility)
        $accessToken = $account->getAccessToken();

        if ($accessToken !== null) {
            return $server->authOAuth($account->getUsername(), $accessToken);
        }

        return $server->authLogin($account->getUsername(), $account->getPassword());
    }

    private function formatRecipients(array $recipients): string {
        $arr = [];

        foreach ($recipients as $address => $name) {
            $arr[] = '=?UTF-8?B?'.base64_encode($name).'?='.' <'.$address.'>';
        }

        return implode(',', $arr);
    }

    private function getPlainTextBody(Email $message): string {
        $html = $message->getDocument()->getBody()->toHTML();
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n\s*\n+/", "\n\n", $text);

        return trim($text);
    }

    private function sendAttachments(Email $message, SMTPServer $server): void {
        $files = $message->getAttachments();

        if (count($files) != 0) {
            $boundary = $message->getBoundary();

            foreach ($files as $fileObj) {
                $fileObj->read();
                $contentChunk = chunk_split($fileObj->getRawData(true));
                $server->sendCommand('--'.$boundary);
                $server->sendCommand('Content-Type: '.$fileObj->getMIME().'; name="'.$fileObj->getName().'"');
                $server->sendCommand('Content-Transfer-Encoding: base64');
                $server->sendCommand('Content-Disposition: attachment; filename="'.$fileObj->getName().'"'.SMTPServer::NL);
                $server->sendCommand($contentChunk);
            }
            $server->sendCommand('--'.$boundary.'--'.SMTPServer::NL);
        }
    }

    private function sendBody(Email $message, SMTPServer $server): void {
        $boundary = $message->getBoundary();
        $server->sendCommand('Content-Type: multipart/mixed; boundary="'.$boundary.'"'.SMTPServer::NL);
        $server->sendCommand('--'.$boundary);
        $server->sendCommand('Content-Type: multipart/alternative; boundary="'.$boundary.'-alt"'.SMTPServer::NL);
        $server->sendCommand('--'.$boundary.'-alt');
        $server->sendCommand('Content-Type: text/plain; charset="UTF-8"');
        $server->sendCommand('Content-Transfer-Encoding: base64'.SMTPServer::NL);
        $server->sendCommand(chunk_split(base64_encode($this->getPlainTextBody($message))));
        $server->sendCommand('--'.$boundary.'-alt');
        $server->sendCommand('Content-Type: text/html; charset="UTF-8"');
        $server->sendCommand('Content-Transfer-Encoding: base64'.SMTPServer::NL);
        $server->sendCommand(chunk_split(base64_encode($message->getDocument()->toHTML())));
        $server->sendCommand('--'.$boundary.'-alt--');
    }

    private function sendHeaders(Email $message, SMTPServer $server, SMTPAccount $acc): void {
        $priorityAsInt = $message->getPriority();
        $priorities = Email::PRIORITIES;
        $priorityHeaderVal = $priorities[$priorityAsInt];

        if ($priorityAsInt == -1) {
            $importanceHeaderVal = 'low';
        } else if ($priorityAsInt == 1) {
            $importanceHeaderVal = 'High';
        } else {
            $importanceHeaderVal = 'normal';
        }

        $server->sendCommand('Priority: '.$priorityHeaderVal);
        $server->sendCommand('Importance: '.$importanceHeaderVal);
        $server->sendCommand('From: =?UTF-8?B?'.base64_encode($acc->getSenderName()).'?= <'.$acc->getAddress().'>');
        $server->sendCommand('To: '.$this->formatRecipients($message->getTo()));
        $server->sendCommand('CC: '.$this->formatRecipients($message->getCC()));
        $server->sendCommand('BCC: '.$this->formatRecipients($message->getBCC()));
        $server->sendCommand('Date:'.date('r (T)'));
        $messageId = '<'.bin2hex(random_bytes(16)).'@'.$acc->getServerAddress().'>';
        $server->sendCommand('Message-ID: '.$messageId);

        // Store the generated ID on the message so callers can read it after send()
        $message->setMessageId($messageId);

        if (strlen($message->getInReplyTo()) > 0) {
            $server->sendCommand('In-Reply-To: '.$message->getInReplyTo());
            $server->sendCommand('References: '.$message->getInReplyTo());
        }

        $server->sendCommand('Subject:'.'=?UTF-8?B?'.base64_encode($message->getSubject()).'?=');
        $server->sendCommand('MIME-Version: 1.0');
    }

    /**
     * Sends 'RCPT TO' for all recipients (To, CC, BCC), tolerating
     * per-recipient rejections.
     *
     * @param Email $message The message being sent.
     * @param SMTPServer $server The connected SMTP server.
     *
     * @return int The total number of recipients accepted by the server.
     */
    private function sendRecipients(Email $message, SMTPServer $server): int {
        $accepted = 0;
        $accepted += $this->sendRecipientsOfType($message->getTo(), $server);
        $accepted += $this->sendRecipientsOfType($message->getCC(), $server);
        $accepted += $this->sendRecipientsOfType($message->getBCC(), $server);

        return $accepted;
    }

    /**
     * Sends 'RCPT TO' for each recipient of a given type, tolerating
     * per-recipient rejections.
     *
     * A 4xx/5xx response to 'RCPT TO' is a per-recipient rejection, not a
     * session failure (RFC 5321). Rejected recipients are skipped (their error
     * state cleared so the next command can be sent) and the remaining
     * recipients are still attempted. A 451 (greylisting) response triggers a
     * single immediate retry.
     *
     * @param array<string, string> $recipients Map of address => name.
     * @param SMTPServer $server The connected SMTP server.
     *
     * @return int The number of recipients accepted by the server.
     */
    private function sendRecipientsOfType(array $recipients, SMTPServer $server): int {
        $accepted = 0;

        foreach ($recipients as $address => $name) {
            // Clear any prior per-recipient rejection so the guard in
            // sendCommand() does not block this RCPT TO.
            $server->clearErrorState();
            $server->sendCommand('RCPT TO: <'.$address.'>');

            if ($server->getLastResponseCode() == 451) {
                // Greylisting: single immediate retry after a brief delay.
                $server->reset();
                sleep(1);
                $server->sendCommand('RCPT TO: <'.$address.'>');
            }

            if ($server->getLastResponseCode() < 400) {
                $accepted++;
            }
            // A 4xx/5xx rejection is left in place until the next iteration's
            // clearErrorState(), so the final rejection's code/response remain
            // available to the caller when every recipient is rejected.
        }

        return $accepted;
    }

    private function trimControlChars(string $str): string {
        $trimmed = trim($str, "\x00..\x20");

        return preg_replace("/(\s*[\r\n]+\s*|\s+)/", ' ', $trimmed);
    }
}
