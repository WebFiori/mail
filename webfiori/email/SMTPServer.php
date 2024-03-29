<?php
namespace webfiori\email;

use webfiori\email\exceptions\SMTPException;
/**
 * A class which can be used to connect to SMTP server and execute commands on it.
 *
 * @author Ibrahim
 * 
 */
class SMTPServer {
    const NL = "\r\n";
    private $isWriting;
    private $lastCommand;
    /**
     * The last message that was sent by email server.
     * 
     * @var string
     * 
     */
    private $lastResponse;
    /**
     * Last received code from server after sending some command.
     * 
     * @var int 
     * 
     */
    private $lastResponseCode;
    /**
     *
     * @var array
     * 
     */
    private $responseLog;
    /**
     * Connection timeout (in minutes)
     * 
     * @var int 
     */
    private $responseTimeout;
    /**
     * The resource that is used to fire commands.
     * 
     * @var resource 
     */
    private $serverCon;
    /**
     * The name of mail server host.
     * 
     * @var string 
     * 
     */
    private $serverHost;
    private $serverOptions;
    /**
     * The port number.
     * 
     * @var int 
     * 
     */
    private $serverPort;
    /**
     * Initiates new instance of the class.
     * 
     * @param string $serverAddress SMTP Server address such as 'smtp.example.com'.
     * 
     * @param int $port SMTP server port such as 25, 465 or 587.
     */
    public function __construct(string $serverAddress, int $port) {
        $this->serverPort = $port;
        $this->serverHost = $serverAddress;
        $this->serverOptions = [];
        $this->responseTimeout = 5;
        $this->lastResponse = '';
        $this->lastResponseCode = 0;
        $this->isWriting = false;
        $this->responseLog = [];
    }

    /**
     * Use plain authorization method to log in the user to SMTP server.
     *
     * This method will attempt to establish a connection to SMTP server if
     * the method 'SMTPServer::connect()' is called.
     *
     * @param string $username The username of SMTP server user.
     *
     * @param string $pass The password of the user.
     *
     * @return bool If the user is authenticated successfully, the method
     * will return true. Other than that, the method will return false.
     *
     * @throws SMTPException
     */
    public function authLogin(string $username, string $pass) : bool {
        if (!$this->isConnected()) {
            $this->connect();

            if (!$this->isConnected()) {
                return false;
            }
        }
        $this->sendCommand('AUTH LOGIN');
        $this->sendCommand(base64_encode($username));
        $this->sendCommand(base64_encode($pass));

        if ($this->getLastResponseCode() == 535) {
            return false;
        }

        return true;
    }

    /**
     * Connects to SMTP server.
     *
     * @return bool If the connection was established and the 'EHLO' command
     * was successfully sent, the method will return true. Other than that, the
     * method will return false.
     *
     * @throws SMTPException
     */
    public function connect() : bool {
        $retVal = true;

        if (!$this->isConnected()) {
            set_error_handler(null);
            $transport = $this->getTransport();

            $this->serverCon = $this->_tryConnect($transport);

            if ($this->serverCon === false) {
                $this->serverCon = $this->_tryConnect('');
            }

            if (is_resource($this->serverCon)) {
                $this->_log('-', 0, $this->read());

                if ($this->getLastResponseCode() != 220) {
                    $this->_log('Connect', 0, 'Server did not respond with code 220 during initial connection.');
                    $lastLog = $this->getLastLogEntry();
                    throw new SMTPException($lastLog['message'], $lastLog['code'], $this->getLog());
                }

                if ($this->sendHello()) {
                    //We might need to switch to secure connection.
                    $retVal = $this->checkStartTls();
                } else {
                    $retVal = false;
                }
            } else {
                $retVal = false;
            }
            restore_error_handler();
        }

        return $retVal;
    }
    /**
     * Returns SMTP server host address.
     * 
     * @return string A string such as 'smtp.example.com'.
     * 
     */
    public function getHost() : string {
        return $this->serverHost;
    }
    /**
     * Returns an array that contains last log entry.
     * 
     * @return array The array will have 4 indices, 'command', 'code',
     * 'message' and 'time'.
     * 
     */
    public function getLastLogEntry() : array {
        $entries = $this->getLog();
        $entriesCount = count($entries);

        if ($entriesCount != 0) {
            return $entries[$entriesCount - 1];
        }

        return [
            'command' => '',
            'code' => 0,
            'message' => '',
            'time' => ''
        ];
    }
    /**
     * Returns the last response message which was sent by the server.
     * 
     * @return string The last response message after executing some command. Default 
     * value is empty string.
     * 
     */
    public function getLastResponse() : string {
        return $this->lastResponse;
    }
    /**
     * Returns last response code that was sent by SMTP server after executing 
     * specific command.
     * 
     * @return int The last response code that was sent by SMTP server after executing 
     * specific command. Default return value is 0.
     * 
     */
    public function getLastResponseCode() : int {
        return $this->lastResponseCode;
    }
    /**
     * Returns the last command which was sent to SMTP server.
     * 
     * @return string The last command which was sent to SMTP server.
     * 
     */
    public function getLastSentCommand() : string {
        return $this->lastCommand;
    }
    /**
     * Returns an array that contains log messages for different events or 
     * commands which was sent to the server.
     * 
     * @return array The array will hold sub-associative arrays. Each array 
     * will have 4 indices, 'command', 'code', 'message' and 'time'
     * 
     */
    public function getLog() : array {
        return $this->responseLog;
    }
    /**
     * Returns SMTP server port number.
     * 
     * @return int Common values are : 25, 465 (SSL) and 586 (TLS).
     * 
     */
    public function getPort() : int {
        return $this->serverPort;
    }
    /**
     * Returns an array that contains server supported commands.
     * 
     * The method will only be able to get the options after sending the 
     * command 'EHLO' to the server. The array will be empty if not 
     * connected to SMTP server.
     * 
     * @return array An array that holds supported SMTP server options.
     * 
     */
    public function getServerOptions() : array {
        return $this->serverOptions;
    }
    /**
     * Returns the time at which the connection will time out if no response 
     * was received in minutes.
     * 
     * @return int Timeout time in minutes.
     * 
     */
    public function getTimeout() : int {
        return $this->responseTimeout;
    }
    /**
     * Checks if the connection is still open or is it closed.
     * 
     * @return bool true if the connection is open.
     * 
     */
    public function isConnected() : bool {
        return is_resource($this->serverCon);
    }
    /**
     * Checks if the server is in message writing mode.
     * 
     * The server will be in writing mode if the command 'DATA' was sent.
     * 
     * @return bool If the server is in message writing mode, the method 
     * will return true. False otherwise.
     * 
     */
    public function isInWritingMode() : bool {
        return $this->isWriting;
    }
    /**
     * Read server response after sending a command to the server.
     * 
     * @return string
     * 
     */
    public function read() : string {
        $message = '';

        while (!feof($this->serverCon)) {
            $str = fgets($this->serverCon);

            if ($str !== false) {
                $message .= $str;

                if (!isset($str[3]) || (isset($str[3]) && $str[3] == ' ')) {
                    break;
                }
            } else {
                $this->_log('-', '0', 'Unable to read server response.');
                break;
            }
        }
        $this->setLastResponseCode($message);

        return $message;
    }

    /**
     * Sends a command to the mail server.
     *
     * @param string $command Any SMTP command which is supported by the server.
     *
     * @return bool The method will return always true if the command was
     * sent. The only case that the method will return false is when it is not
     * connected to the server.
     *
     * @throws SMTPException
     */
    public function sendCommand(string $command) : bool {
        $this->lastCommand = explode(' ', $command)[0];

        if ($this->lastResponseCode >= 400) {
            throw new SMTPException('Unable to send SMTP commend "'.$command.'" due to '
                    .'error code '.$this->lastResponseCode.' caused by last command. '
                    .'Error message: "'.$this->lastResponse.'".', $this->lastResponseCode, $this->getLog());
        }

        if ($this->isConnected()) {
            fwrite($this->serverCon, $command.self::NL);

            if (!$this->isInWritingMode()) {
                $response = trim($this->read());
                $this->lastResponse = $response;
                $this->_log($command, $this->getLastResponseCode(), $response);
            } else {
                $this->_log($command, 0, '-');
            }

            if ($command == 'DATA') {
                $this->isWriting = true;
            }

            if ($command == self::NL.'.') {
                $this->isWriting = false;
                $response = trim($this->read());
                $this->lastResponse = $response;
                $this->_log($command, $this->getLastResponseCode(), $response);
            }

            return true;
        } else {
            $this->_log($command, 0, '');

            return false;
        }
    }

    /**
     * Sends 'EHLO' command to SMTP server.
     *
     * The developer does not have to call this method manually as its
     * called when connecting to SMTP server.
     *
     * @return bool If the command was sent successfully, the method will
     * return true. Other than that, the method will return false.
     *
     * @throws SMTPException
     */
    public function sendHello() : bool {
        if ($this->sendCommand('EHLO '.$this->getHost())) {
            $this->_parseHelloResponse($this->getLastResponse());

            return true;
        }

        return false;
    }
    /**
     * Sets the timeout time of the connection.
     * 
     * @param int $val The value of timeout (in minutes). The timeout will be updated 
     * only if the connection is not yet established and the given value is grater 
     * than 0.
     * 
     */
    public function setTimeout(int $val) {
        if ($val >= 1 && !$this->isConnected()) {
            $this->responseTimeout = $val;
        }
    }
    private function _log($command, $code, $message) {
        $this->responseLog[] = [
            'command' => $command,
            'code' => $code,
            'message' => $message,
            'time' => date('Y-m-d H:i:s')
        ];
    }
    private function _parseHelloResponse($response) {
        $split = explode(self::NL, $response);
        $index = 0;
        $this->serverOptions = [];

        foreach ($split as $part) {
            //Index 0 will hold server address
            if ($index != 0) {
                $xPart = substr($part, 4);
                $this->serverOptions[] = $xPart;
            }
            $index++;
        }
    }
    private function _tryConnect($protocol) {
        $host = $this->getHost();
        $portNum = $this->getPort();
        $err = 0;
        $errStr = '';
        $timeout = $this->getTimeout();

        if (function_exists('stream_socket_client')) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,

                    'crypto_type' => STREAM_CRYPTO_METHOD_TLSv1_2_SERVER
                ]
            ]);


            $this->_log('Connect', 0, 'Trying to open connection to the server using "stream_socket_client"...');
            $conn = @stream_socket_client($protocol.$host.':'.$portNum, $err, $errStr, $timeout * 60, STREAM_CLIENT_CONNECT, $context);
        } else {
            $this->_log('Connect', 0, 'Trying to open connection to the server using "fsockopen"...');
            $conn = fsockopen($protocol.$this->serverHost, $portNum, $err, $errStr, $timeout * 60);
        }

        if (!is_resource($conn)) {
            if (strlen($errStr) == 0) {
                $this->_log('Connect', $err, 'Failed to open connection due to unspecified error.');
            } else {
                $this->_log('Connect', $err, 'Failed to open connection: '.$errStr);
            }
        } else {
            $this->_log('Connect', 0, 'Connection opened.');
        }

        return $conn;
    }
    private function checkStartTls() : bool {
        if (in_array('STARTTLS', $this->getServerOptions())) {
            if ($this->switchToTls()) {
                $this->sendHello();

                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
    private function getTransport() : string {
        $port = $this->getPort();

        if ($port == 465) {
            return "ssl://";
        } else if ($port == 587) {
            return "tls://";
        }

        return '';
    }
    /**
     * Sets the code that was the result of executing SMTP command.
     * 
     * @param string $serverResponseMessage The last message which was sent by 
     * the server after executing specific command.
     * 
     */
    private function setLastResponseCode(string $serverResponseMessage) {
        if (strlen($serverResponseMessage) != 0) {
            $firstNum = $serverResponseMessage[0];
            $firstAsInt = intval($firstNum);

            if ($firstAsInt != 0) {
                $secNum = $serverResponseMessage[1];
                $thirdNum = $serverResponseMessage[2];
                $this->lastResponseCode = intval($firstNum) * 100 + (intval($secNum * 10)) + (intval($thirdNum));
            }
        }
    }
    private function switchToTls() : bool {
        $this->sendCommand('STARTTLS');
        $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;

        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }


        $success = stream_socket_enable_crypto(
            $this->serverCon,
            true,
            $cryptoMethod
        );

        return $success === true;
    }
}
