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
                $this->account->getPort()
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

            $this->sendRecipients($message, $server);
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
        $server->sendCommand('Content-Transfer-Encoding: quoted-printable'.SMTPServer::NL);
        $server->sendCommand($this->getPlainTextBody($message));
        $server->sendCommand('--'.$boundary.'-alt');
        $server->sendCommand('Content-Type: text/html; charset="UTF-8"');
        $server->sendCommand('Content-Transfer-Encoding: quoted-printable'.SMTPServer::NL);
        $server->sendCommand($this->trimControlChars($message->getDocument()->toHTML()));
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
        $server->sendCommand('Subject:'.'=?UTF-8?B?'.base64_encode($message->getSubject()).'?=');
        $server->sendCommand('MIME-Version: 1.0');
    }

    private function sendRecipients(Email $message, SMTPServer $server): void {
        $this->sendRecipientsOfType($message->getTo(), $server);
        $this->sendRecipientsOfType($message->getCC(), $server);
        $this->sendRecipientsOfType($message->getBCC(), $server);
    }

    private function sendRecipientsOfType(array $recipients, SMTPServer $server): void {
        foreach ($recipients as $address => $name) {
            $server->sendCommand('RCPT TO: <'.$address.'>');

            if ($server->getLastResponseCode() == 451) {
                $server->reset();
                sleep(1);
                $server->sendCommand('RCPT TO: <'.$address.'>');
            }
        }
    }

    private function trimControlChars(string $str): string {
        $trimmed = trim($str, "\x00..\x20");

        return preg_replace("/(\s*[\r\n]+\s*|\s+)/", ' ', $trimmed);
    }
}
