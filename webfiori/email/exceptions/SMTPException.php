<?php
namespace webfiori\email\exceptions;

use Exception;
use Throwable;

/**
 * An exception which is thrown to indicate that something went wrong when 
 * sending an email message using SMTP.
 *
 * @author Ibrahim
 */
class SMTPException extends Exception {
    private $logArr;
    public function __construct(string $message = "", int $code = 0, array $log = [], Throwable|null $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->logArr = $log;
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
        return $this->logArr;
    }
}
