<?php
namespace WebFiori\Tests\Mail;

/**
 * A fake SMTP server for testing. Listens on localhost, responds to SMTP
 * commands. Runs as a background process.
 */
class FakeSMTPServer {
    private int $port;
    private bool $rejectAuth;
    private ?int $pid = null;

    public function __construct(int $port = 2525) {
        $this->port = $port;
        $this->rejectAuth = false;
    }

    public function setRejectAuth(bool $reject): void {
        $this->rejectAuth = $reject;
    }

    public function getPort(): int {
        return $this->port;
    }

    /**
     * Start the server in a forked process.
     */
    public function start(): void {
        $port = $this->port;
        $rejectAuth = $this->rejectAuth;

        $this->pid = pcntl_fork();

        if ($this->pid === -1) {
            throw new \RuntimeException("Could not fork process for fake SMTP server");
        }

        if ($this->pid === 0) {
            // Child process - run the server
            $this->serve($port, $rejectAuth);
            exit(0);
        }

        // Parent process - give the server time to bind
        usleep(200000); // 200ms
    }

    /**
     * Stop the server.
     */
    public function stop(): void {
        if ($this->pid !== null && $this->pid > 0) {
            posix_kill($this->pid, SIGTERM);
            pcntl_waitpid($this->pid, $status);
            $this->pid = null;
        }
    }

    /**
     * Run the server loop (called in child process).
     */
    private function serve(int $port, bool $rejectAuth): void {
        // Handle SIGTERM gracefully
        $running = true;
        pcntl_signal(SIGTERM, function () use (&$running) {
            $running = false;
        });

        $socket = stream_socket_server(
            "tcp://127.0.0.1:$port",
            $errno,
            $errstr
        );

        if (!$socket) {
            exit(1);
        }

        stream_set_blocking($socket, false);

        while ($running) {
            pcntl_signal_dispatch();

            $conn = @stream_socket_accept($socket, 1);

            if ($conn) {
                self::handleConnection($conn, $rejectAuth);
                fclose($conn);
            }
        }

        fclose($socket);
    }

    private static function handleConnection($conn, bool $rejectAuth): void {
        stream_set_timeout($conn, 5);

        // Send greeting
        fwrite($conn, "220 fake.smtp.local ESMTP FakeSMTP\r\n");

        $inData = false;
        $messageData = '';

        while (!feof($conn)) {
            $line = fgets($conn, 4096);

            if ($line === false) {
                break;
            }

            $line = rtrim($line, "\r\n");

            if ($inData) {
                if ($line === '.') {
                    $inData = false;
                    fwrite($conn, "250 OK: message queued\r\n");
                } else {
                    $messageData .= $line . "\r\n";
                }
                continue;
            }

            $cmd = strtoupper(explode(' ', $line)[0]);

            switch ($cmd) {
                case 'EHLO':
                case 'HELO':
                    fwrite($conn, "250-fake.smtp.local Hello\r\n");
                    fwrite($conn, "250-SIZE 35882577\r\n");
                    fwrite($conn, "250-AUTH LOGIN PLAIN XOAUTH2\r\n");
                    fwrite($conn, "250 OK\r\n");
                    break;

                case 'AUTH':
                    if ($rejectAuth) {
                        fwrite($conn, "535 Incorrect authentication data\r\n");
                    } else {
                        if (strpos($line, 'LOGIN') !== false) {
                            fwrite($conn, "334 VXNlcm5hbWU6\r\n");
                            // Read username
                            fgets($conn, 4096);
                            fwrite($conn, "334 UGFzc3dvcmQ6\r\n");
                            // Read password
                            fgets($conn, 4096);
                            fwrite($conn, "235 Authentication successful\r\n");
                        } else {
                            fwrite($conn, "235 Authentication successful\r\n");
                        }
                    }
                    break;

                case 'MAIL':
                    fwrite($conn, "250 OK\r\n");
                    break;

                case 'RCPT':
                    fwrite($conn, "250 OK\r\n");
                    break;

                case 'DATA':
                    fwrite($conn, "354 Start mail input; end with <CRLF>.<CRLF>\r\n");
                    $inData = true;
                    break;

                case 'QUIT':
                    fwrite($conn, "221 fake.smtp.local closing connection\r\n");
                    return;

                case 'STARTTLS':
                    fwrite($conn, "502 Command not implemented\r\n");
                    break;

                case 'RSET':
                    fwrite($conn, "250 OK\r\n");
                    break;

                default:
                    // For AUTH LOGIN, the base64-encoded user/pass come as raw lines
                    // They won't match any command, just acknowledge them
                    fwrite($conn, "250 OK\r\n");
                    break;
            }
        }
    }
}
