<?php
namespace webfiori\email;

use webfiori\email\exceptions\SMTPException;
use webfiori\file\exceptions\FileException;
use webfiori\file\File;
use webfiori\ui\exceptions\InvalidNodeNameException;
use webfiori\ui\HTMLDoc;
use webfiori\ui\HTMLNode;
/**
 * A class that can be used to write HTML formatted Email messages.
 *
 * @author Ibrahim
 * @version 1.0.6
 */
class Email {
    /**
     * A constant that colds the possible values for the header 'Priority'. 
     * 
     * @see https://tools.ietf.org/html/rfc4021#page-33
     * 
     * @since 2.0
     */
    const PRIORITIES = [
        -1 => 'non-urgent',
        0 => 'normal',
        1 => 'urgent'
    ];
    private $afterSendPool;
    /**
     * An array that contains an objects of type 'File' or 
     * file path. 
     * 
     * @var array 
     * 
     * @since 2.0
     */
    private $attachments;
    /**
     * An array that holds callbacks which will get executed before sending
     * the message.
     * 
     * @var array
     * 
     * @since 1.0.6
     */
    private $beforeSendPool;
    /**
     * A boundary variable used to separate email message parts.
     * 
     * @var string
     * 
     * @since 2.0
     */
    private $boundry;
    /**
     *
     * @var HTMLDoc 
     * 
     * @since 1.0 
     */
    private $document;
    private $inReplyTo;

    private $log;
    private $priority;
    /**
     * 
     * @var array
     * 
     * @since 2.0
     */
    private $receiversArr;
    /**
     * SMTP account that will be used to send the message.
     * 
     * @var SMTPAccount
     * 
     * @since 1.0
     */
    private $smtpAcc;
    /**
     * 
     * @var SMTPServer|null
     * 
     * @since 2.0
     */
    private $smtpServer;
    /**
     * The subject of the email message.
     * 
     * @var string 
     * 
     * @since 2.0
     */
    private $subject;
    /**
     * Creates new instance of the class.
     * 
     * @param SMTPAccount $sendAccount The SMTP connection that will be
     * used to send the message.
     * 
     * @since 1.0
     */
    public function __construct(SMTPAccount $sendAccount = null) {
        $this->log = [];
        $this->priority = 0;
        $this->subject = 'Hello Email Message';
        $this->boundry = hash('sha256', date('Y-m-d\TH:i:sO'));
        $this->receiversArr = [
            'cc' => [],
            'bcc' => [],
            'to' => []
        ];
        $this->attachments = [];
        $this->inReplyTo = [];
        $this->beforeSendPool = [];
        $this->afterSendPool = [];
        $this->document = new HTMLDoc();

        if ($sendAccount !== null) {
            $this->setSMTPAccount($sendAccount);
        }
    }
    /**
     * Adds a callback to execute after the message is sent.
     * 
     * @param callable $callback A function that will get executed before sending
     * the message. Note that the first parameter of the callback will be always
     * the message (e.g. function (EmailMessage $message) {})
     * 
     * @param array $extraParams An optional array of extra parameters that will
     * be passed to the callback.
     * 
     * @since 1.0.6
     */
    public function addAfterSend(callable $callback, array $extraParams = []) {
        $this->beforeSendPool[] = [
            'func' => $callback,
            'params' => array_merge([$this], $extraParams),
            'executed' => false
        ];
    }
    /**
     * Adds a file as email attachment.
     * 
     * @param File|string $fileObjOrFilePath An object of type 'File'. This also can 
     * be the absolute path to a file in the file system.
     * 
     * @return bool If the file is added, the method will return true. 
     * Other than that, the method will return false.
     * 
     * @since 2.0
     */
    public function addAttachment($fileObjOrFilePath) : bool {
        $retVal = false;

        $type = gettype($fileObjOrFilePath);

        if ($type == 'string') {
            $fileObj = new File($fileObjOrFilePath);
        } else if ($fileObjOrFilePath instanceof File) {
            $fileObj = $fileObjOrFilePath;
        } else {
            $fileObj = null;
        }

        if ($fileObj instanceof File && $fileObj->isExist()) {
            $this->attachments[] = $fileObj;
            $retVal = true;
        }

        return $retVal;
    }
    /**
     * Adds new receiver address to the list of 'bcc' receivers.
     * 
     * @param string $address The email address of the receiver (such as 'example@example.com').
     * 
     * @param string|null $name An optional receiver name. If not provided, the 
     * email address is used as name.
     * 
     * @return bool If the address is added, the method will return 
     * true. False otherwise.
     * 
     * @since 2.0
     */
    public function addBCC(string $address, string $name = null): bool {
        if ($name === null) {
            $name = $address;
        }

        return $this->addAddressHelper($address, $name, 'bcc');
    }
    /**
     * Adds a callback to execute before the message is sent.
     * 
     * @param callable $callback A function that will get executed before sending
     * the message. Note that the first parameter of the callback will be always
     * the message (e.g. function (EmailMessage $message) {})
     * 
     * @param array $extraParams An optional array of extra parameters that will
     * be passed to the callback.
     * 
     * @since 1.0.6
     */
    public function addBeforeSend(callable $callback, array $extraParams = []) {
        $this->beforeSendPool[] = [
            'func' => $callback,
            'params' => array_merge([$this], $extraParams),
            'executed' => false
        ];
    }
    /**
     * Adds new receiver address to the list of 'cc' receivers.
     * 
     * @param string $address The email address of the receiver (such as 'example@example.com').
     * 
     * @param string $name An optional receiver name. If not provided, the 
     * email address is used as name.
     * 
     * @return bool If the address is added, the method will return 
     * true. False otherwise.
     * 
     * @since 2.0
     */
    public function addCC(string $address, string $name = null) : bool {
        return $this->addAddressHelper($address, $name, 'cc');
    }
    /**
     * Adds new receiver address to the list of 'to' receivers.
     * 
     * @param string $address The email address of the receiver (such as 'example@example.com').
     * 
     * @param string $name An optional receiver name. If not provided, the 
     * email address is used as name.
     * 
     * @return bool If the address is added, the method will return 
     * true. False otherwise.
     * 
     * @since 2.0
     */
    public function addTo(string $address, string $name = null) : bool {
        if ($name === null) {
            $name = $address;
        }

        return $this->addAddressHelper($address, $name, 'to');
    }
    /**
     * Returns an array that contains the information of all added attachments.
     * 
     * @return array An array that contains the information of all added attachments.
     * Each index will contain the attachment as object of type 'File'.
     */
    public function getAttachments() : array {
        return $this->attachments;
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive a blind carbon copy of the message.
     * 
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * 
     * @return array An array that contains receivers information.
     * 
     * @since 1.0.2
     */
    public function getBCC() : array {
        return $this->receiversArr['bcc'];
    }
    /**
     * Returns a string that contains the names and the addresses 
     * of people who will receive a blind carbon copy of the message.
     * 
     * The format of the string will be as follows:
     * <p>NAME_1 &lt;ADDRESS_1&gt;, NAME_2 &lt;ADDRESS_2&gt; ...</p>
     * 
     * @return string A string that contains receivers information.
     * 
     * @since 1.0.3
     */
    public function getBCCStr() : string {
        return $this->getReceiversStrHelper('bcc');
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive a carbon copy of the message.
     * 
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * 
     * @return array An array that contains receivers information.
     * 
     * @since 1.0.2
     */
    public function getCC() : array {
        return $this->receiversArr['cc'];
    }
    /**
     * Returns a string that contains the names and the addresses 
     * of people who will receive a carbon copy of the message.
     * 
     * The format of the string will be as follows:
     * <p>NAME_1 &lt;ADDRESS_1&gt;, NAME_2 &lt;ADDRESS_2&gt; ...</p>
     * 
     * @return string A string that contains receivers information.
     * 
     * @since 1.0.3
     */
    public function getCCStr() : string {
        return $this->getReceiversStrHelper('cc');
    }
    /**
     * Returns a child node given its ID.
     * 
     * @param string $id The ID of the child.
     * 
     * @return null|HTMLNode The method returns an object of type HTMLNode. 
     * if found. If no node has the given ID, the method will return null.
     * 
     * @since 1.0.5
     */
    public function getChildByID(string $id) {
        return $this->getDocument()->getChildByID($id);
    }
    /**
     * Returns the document that is associated with the page.
     * 
     * @return HTMLDoc An object of type 'HTMLDoc'.
     * 
     * @since 1.0.5
     */
    public function getDocument() : HTMLDoc {
        return $this->document;
    }
    /**
     * Returns the language code of the email.
     * 
     * @return string|null Two digit language code. In case language is not set, the 
     * method will return null
     * 
     * @since 1.0.5
     */
    public function getLang() {
        return $this->getDocument()->getLanguage();
    }
    /**
     * Returns an array that contains log messages which are generated 
     * from sending SMTP commands.
     * 
     * @return array The array will be indexed. In every index, there 
     * will be a sub-associative array with the following indices:
     * <ul>
     * <li>command</li>
     * <li>response-code</li>
     * <li>response-message</li>
     * </ul>
     * 
     * @since 1.0.4
     */
    public function getLog() : array {
        return $this->getSMTPServer()->getLog();
    }
    /**
     * Returns the priority of the message.
     * 
     * @return int The priority of the message. -1 for non-urgent, 0 
     * for normal and 1 for urgent. Default value is 0.
     * 
     * @since 2.0
     */
    public function getPriority() : int {
        return $this->priority;
    }
    /**
     * 
     * @return SMTPAccount
     */
    public function getSMTPAccount() : SMTPAccount {
        return $this->smtpAcc;
    }
    /**
     * Returns an object that holds SMTP server information.
     * 
     * The returned instance can be used to access SMTP server messages log 
     * to see if the message was transfered or not. Note that the 
     * connection to the server will only be established once the 
     * method 'EmailMessage::send()'.
     * 
     * @return SMTPServer An instance which represents SMTP server.
     * 
     * @since 1.0.5
     */
    public function getSMTPServer() : SMTPServer {
        return $this->smtpServer;
    }
    /**
     * Returns the subject of the email.
     * 
     * @return string The subject of the email. Default return value is 
     * 'Hello From WebFiori Framework'.
     * 
     * @since 2.0
     */
    public function getSubject() : string {
        return $this->subject;
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive an original copy of the message.
     * 
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * 
     * @return array An array that contains receivers information.
     * 
     * @since 1.0.2
     */
    public function getTo() : array {
        return $this->receiversArr['to'];
    }
    /**
     * Returns a string that contains the names and the addresses 
     * of people who will receive an original copy of the message.
     * 
     * The format of the string will be as follows:
     * <p>NAME_1 &lt;ADDRESS_1&gt;, NAME_2 &lt;ADDRESS_2&gt; ...</p>
     * 
     * @return string A string that contains receivers information.
     * 
     * @since 1.0.3
     */
    public function getToStr() : string {
        return $this->getReceiversStrHelper('to');
    }

    /**
     * Adds a child node inside the body of a node given its ID.
     *
     * @param HTMLNode|string $node The node that will be inserted. Also,
     * this can be the tag name of the node such as 'div'.
     *
     * @param string|null $parentNodeId The ID of the node that the given node
     * will be inserted to. If null is given, the node will be added directly inside
     * the element &lt;body&gt;. Default value is null.
     *
     * @return HTMLNode|null The method will return the inserted
     * node if it was inserted. If it is not, the method will return null.
     *
     * @throws InvalidNodeNameException
     * @since 1.0.5
     */
    public function insert($node, string $parentNodeId = null) {
        if (gettype($node) == 'string') {
            $node = new HTMLNode($node);
        }
        $parent = $parentNodeId !== null ? $this->getChildByID($parentNodeId) 
                : $this->getDocument()->getBody();

        if ($parent !== null) {
            $parent->addChild($node);

            return $node;
        }

        return null;
    }
    /**
     * Execute all the callbacks which are set to execute after sending the
     * message.
     */
    public function runAfterSend() {
        foreach ($this->afterSendPool as $callArr) {
            call_user_func_array($callArr['func'], $callArr['params']);
            $callArr['executed'] = true;
        }
    }
    /**
     * Execute all the callbacks which are set to execute before sending the
     * message.
     */
    public function runBeforeSend() {
        foreach ($this->beforeSendPool as $callArr) {
            call_user_func_array($callArr['func'], $callArr['params']);
            $callArr['executed'] = true;
        }
    }
    /**
     * Saves the email as HTML web page.
     * 
     * This method will attempt to create a folder which has same subject
     * as the email. Inside the folder, it will attempt to create HTML
     * web page which holds the actual email. The name of the file
     * will be date and time at which the file was created at.
     * 
     * @param string $folderPath The location at which the email will be
     * stored at.
     */
    public function storeEmail(string $folderPath) {
        
        $this->runBeforeSend();
        $acc = $this->getSMTPAccount();
        
        $headersTable = new HeadersTable();
        $headersTable->addHeader('Importance', $this->priorityCommandHelper());
        $headersTable->addHeader('From', $acc->getSenderName().' <'.$acc->getAddress().'>');
        $headersTable->addHeader('To', $this->getReceiversStrHelper('to', false));
        $headersTable->addHeader('CC', $this->getReceiversStrHelper('cc', false));
        $headersTable->addHeader('BCC', $this->getReceiversStrHelper('bcc', false));
        $headersTable->addHeader('Date', date('r (T)'));
        $headersTable->addHeader('Subject', $this->getSubject());
        $atts = '';
        foreach ($this->getAttachments() as $fileObj) {
            $atts .= $fileObj->getName().' ';
        }
        $headersTable->addHeader('Attachments', $atts);
        $this->runAfterSend();
        $this->getDocument()->getBody()->insert(new HTMLNode('hr'), 0);
        $this->getDocument()->getBody()->insert($headersTable, 0);
        
        $name = str_replace(':?\\//*<>|', '', $this->getSubject());
        
        $file = new File($folderPath.DIRECTORY_SEPARATOR.$this->getSubject().DIRECTORY_SEPARATOR.date('Y-m-d H-i-s').'.html');
        $file->setRawData($this->getDocument()->toHTML(true).'');
        $file->write(false, true);
    }
    /**
     * Sends the message.
     * 
     * Note that if in testing environment, the method will attempt to store
     * the email as HTML web page. Testing environment is set when the constant
     * EMAIL_TESTING is defined and set to true in addition to having the
     * constant EMAIL_TESTING_PATH is defined.
     * 
     * @since 1.0
     */
    public function send() {
        if (defined('EMAIL_TESTING') && EMAIL_TESTING === true) {
            //Testing mode. Store email instead of sending.
            if (!defined('EMAIL_TESTING_PATH') || !File::isDirectory(EMAIL_TESTING_PATH, true)) {
                throw new FileException('"EMAIL_TESTING_PATH" is not valid.');
            }
            $this->storeEmail(EMAIL_TESTING_PATH);
            
            return;
        }
        $acc = $this->getSMTPAccount();

        $this->runBeforeSend();
        $server = $this->getSMTPServer();
        
        if ($server->authLogin($acc->getUsername(), $acc->getPassword())) {
            $server->sendCommand('MAIL FROM: <'.$acc->getAddress().'>');
            $this->receiversCommandHelper('to');
            $this->receiversCommandHelper('cc');
            $this->receiversCommandHelper('bcc');
            $server->sendCommand('DATA');
            $importanceHeaderVal = $this->priorityCommandHelper();

            $server->sendCommand('Content-Transfer-Encoding: quoted-printable');
            $server->sendCommand('Importance: '.$importanceHeaderVal);
            $server->sendCommand('From: =?UTF-8?B?'.base64_encode($acc->getSenderName()).'?= <'.$acc->getAddress().'>');
            $server->sendCommand('To: '.$this->getReceiversStrHelper('to'), false);
            $server->sendCommand('CC: '.$this->getReceiversStrHelper('cc'), false);
            $server->sendCommand('BCC: '.$this->getReceiversStrHelper('bcc'), false);
            $server->sendCommand('Date:'.date('r (T)'));
            $server->sendCommand('Subject:'.'=?UTF-8?B?'.base64_encode($this->getSubject()).'?=');
            $server->sendCommand('MIME-Version: 1.0');
            $server->sendCommand('Content-Type: multipart/mixed; boundary="'.$this->boundry.'"'.SMTPServer::NL);
            $server->sendCommand('--'.$this->boundry);
            $server->sendCommand('Content-Type: text/html; charset="UTF-8"'.SMTPServer::NL);
            $server->sendCommand($this->trimControlChars($this->getDocument()->toHTML()));
            $this->appendAttachments();
            $server->sendCommand(SMTPServer::NL.'.');
            $server->sendCommand('QUIT');
            $this->runAfterSend();
        } else {
            throw new SMTPException('Unable to login to SMTP server: '.$server->getLastResponse(), $server->getLastResponseCode());
        }
    }
    /**
     * Sets the display language of the email.
     * 
     * The length of the given string must be 2 characters in order to set the 
     * language code.
     * 
     * @param string $langCode a two characters language code such as AR or EN. Default 
     * value is 'EN'.
     * 
     */
    public function setLang(string $langCode = 'EN') : bool {
        $langU = strtoupper(trim($langCode));

        if (strlen($langU) == 2) {
            $this->getDocument()->setLanguage($langU);
            
            return true;
        }
        
        return false;
    }
    /**
     * Sets the priority of the message.
     * 
     * @param int $messagePriority The priority of the message. -1 for non-urgent, 0 
     * for normal and 1 for urgent. If the passed value is greater than 1, 
     * then 1 will be used. If the passed value is less than -1, then -1 is 
     * used. Other than that, 0 will be used.
     * 
     * @since 2.0
     */
    public function setPriority(int $messagePriority) {
        if ($messagePriority <= -1) {
            $this->priority = -1;
        } else if ($messagePriority >= 1) {
            $this->priority = 1;
        } else {
            $this->priority = 0;
        }
    }

    /**
     * Sets SMTP account that will be used by SMTP server.
     *
     * @param SMTPAccount $account An account that holds connection information.
     *
     */
    public function setSMTPAccount(SMTPAccount $account) {
        $this->smtpAcc = $account;
        $this->smtpServer = new SMTPServer($account->getServerAddress(), $account->getPort());
    }
    /**
     * Sets the subject of the message.
     * 
     * @param string $subject Email subject.
     * 
     * @since 2.0
     */
    public function setSubject(string $subject) {
        $trimmed = $this->trimControlChars($subject);

        if (strlen($trimmed) > 0) {
            $this->subject = $trimmed;
            $this->getDocument()->getHeadNode()->setPageTitle($trimmed);
        }
    }
    private function addAddressHelper(string $address, string $name, string $type = 'to') : bool {
        $nameTrimmed = $this->trimControlChars(str_replace('<', '', str_replace('>', '', $name)));
        $addressTrimmed = $this->trimControlChars(str_replace('<', '', str_replace('>', '', $address)));

        if (strlen($nameTrimmed) == 0) {
            $nameTrimmed = $addressTrimmed;
        }

        if (strlen($addressTrimmed) != 0 && in_array($type, ['cc', 'bcc', 'to'])) {
            $this->receiversArr[$type][$addressTrimmed] = $nameTrimmed;

            return true;
        }

        return false;
    }

    /**
     * A method that is used to include email attachments.
     *
     * @throws SMTPException
     * @since 1.3
     */
    private function appendAttachments() {
        $files = $this->getAttachments();

        if (count($files) != 0) {
            $server = $this->getSMTPServer();

            foreach ($files as $fileObj) {
                $fileObj->read();
                $contentChunk = chunk_split($fileObj->getRawData(true));
                $server->sendCommand('--'.$this->boundry);
                $server->sendCommand('Content-Type: '.$fileObj->getMIME().'; name="'.$fileObj->getName().'"');
                $server->sendCommand('Content-Transfer-Encoding: base64');
                $server->sendCommand('Content-Disposition: attachment; filename="'.$fileObj->getName().'"'.SMTPServer::NL);
                $server->sendCommand($contentChunk);
            }
            $server->sendCommand('--'.$this->boundry.'--'.SMTPServer::NL);
        }
    }
    private function getReceiversStrHelper(string $type, bool $encode = true) : string {
        $arr = [];

        foreach ($this->receiversArr[$type] as $address => $name) {
            if ($encode === true) {
                $arr[] = '=?UTF-8?B?'.base64_encode($name).'?='.' <'.$address.'>';
            } else {
                $arr[] = $name.' <'.$address.'>';
            }
        }

        return implode(',', $arr);
    }

    /**
     * @throws SMTPException
     */
    private function priorityCommandHelper(): string {
        $priorityAsInt = $this->getPriority();
        $priorityHeaderVal = self::PRIORITIES[$priorityAsInt];

        if ($priorityAsInt == -1) {
            $importanceHeaderVal = 'low';
        } else if ($priorityAsInt == 1) {
            $importanceHeaderVal = 'High';
        } else {
            $importanceHeaderVal = 'normal';
        }
        $this->getSMTPServer()->sendCommand('Priority: '.$priorityHeaderVal);

        return $importanceHeaderVal;
    }

    /**
     * @throws SMTPException
     */
    private function receiversCommandHelper($type) {
        $server = $this->getSMTPServer();

        foreach ($this->receiversArr[$type] as $address => $name) {
            $server->sendCommand('RCPT TO: <'.$address.'>');
        }
    }
    /**
     * Removes control characters from the start and end of string in addition 
     * to white spaces.
     * 
     * @param string $str The string that will be trimmed.
     * 
     * @return string The string after its control characters trimmed.
     */
    private function trimControlChars(string $str) : string {
        return trim($str, "\x00..\x20");
    }
}
