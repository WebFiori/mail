<?php
namespace webfiori\email;

use webfiori\email\exceptions\SMTPException;
use webfiori\file\File;
use webfiori\ui\HTMLDoc;
use webfiori\ui\HTMLNode;
/**
 * A class that can be used to write HTML formatted Email messages.
 *
 * @author Ibrahim
 * @version 1.0.6
 */
class EmailMessage {
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
    private $afterSendPool;
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
     * @var string
     * 
     * @since 1.0.5
     */
    private $contentLang;
    /**
     *
     * @var HTMLDoc 
     * 
     * @since 1.0 
     */
    private $document;
    private $inReplyTo;

    private $log;
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
    private $priority;
    /**
     * Creates new instance of the class.
     * 
     * @param string $sendAccount The SMTP connection that will be 
     * used to send the message.
     * 
     * @since 1.0
     */
    public function __construct(SMTPAccount $sendAccount) {
        $this->log = [];
        $this->priority = 0;
        $this->subject = 'Hello From WebFiori Framework';
        $this->boundry = hash('sha256', date(DATE_ISO8601));
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

        $this->smtpAcc = $sendAccount;
        $this->smtpServer = new SMTPServer($sendAccount->getServerAddress(), $sendAccount->getPort());
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
     * Returns an array that contains the information of all added attachments.
     * 
     * @return array An array that contains the information of all added attachments.
     * Each index will contain the attachment as object of type File.
     */
    public function getAttachments() {
        return $this->attachments;
    }
    /**
     * Adds new receiver address to the list of 'bcc' receivers.
     * 
     * @param string $address The email address of the receiver (such as 'example@example.com').
     * 
     * @param string $name An optional receiver name. If not provided, the 
     * email address is used as name.
     * 
     * @return boolean If the address is added, the method will return 
     * true. False otherwise.
     * 
     * @since 2.0
     */
    public function addBCC(string $address, $name = null) {
        return $this->_addAddress($address, $name, 'bcc');
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
     * Adds new receiver address to the list of 'cc' receivers.
     * 
     * @param string $address The email address of the receiver (such as 'example@example.com').
     * 
     * @param string $name An optional receiver name. If not provided, the 
     * email address is used as name.
     * 
     * @return boolean If the address is added, the method will return 
     * true. False otherwise.
     * 
     * @since 2.0
     */
    public function addCC(string $address, $name = null) {
        return $this->_addAddress($address, $name, 'cc');
    }
    /**
     * Adds new receiver address to the list of 'to' receivers.
     * 
     * @param string $address The email address of the receiver (such as 'example@example.com').
     * 
     * @param string $name An optional receiver name. If not provided, the 
     * email address is used as name.
     * 
     * @return boolean If the address is added, the method will return 
     * true. False otherwise.
     * 
     * @since 2.0
     */
    public function addTo(string $address, string $name = null) {
        return $this->_addAddress($address, $name, 'to');
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
        return $this->_getReceiversStr('bcc');
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
        return $this->_getReceiversStr('cc');
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
     * Returns the language code of the email.
     * 
     * @return string|null Two digit language code. In case language is not set, the 
     * method will return null
     * 
     * @since 1.0.5
     */
    public function getLang() {
        return $this->contentLang;
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
        return $this->smtpServer->getLog();
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
        return $this->_getReceiversStr('to');
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
     * @since 1.0.5
     */
    public function insert($node, $parentNodeId = null) {
        if (gettype($node) == 'string') {
            $node = new HTMLNode($node);
        }
        $parent = $parentNodeId !== null ? $this->getChildByID($parentNodeId) 
                : $this->getDocument()->getBody();

        if ($parent !== null) {
            $parent->addChild($node);

            return $node;
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
     * Sends the message and set message instance to null.
     * 
     * @since 1.0
     */
    public function send() {
        
        $acc = $this->getSMTPAccount();
        
        $this->runBeforeSend();
        if ($this->smtpServer->authLogin($acc->getUsername(), $acc->getPassword())) {
            $this->smtpServer->sendCommand('MAIL FROM: <'.$acc->getAddress().'>');
            $this->_receiversCommand('to');
            $this->_receiversCommand('cc');
            $this->_receiversCommand('bcc');
            $this->smtpServer->sendCommand('DATA');
            $importanceHeaderVal = $this->_priorityCommand();

            $this->smtpServer->sendCommand('Content-Transfer-Encoding: quoted-printable');
            $this->smtpServer->sendCommand('Importance: '.$importanceHeaderVal);
            $this->smtpServer->sendCommand('From: =?UTF-8?B?'.base64_encode($acc->getSenderName()).'?= <'.$acc->getAddress().'>');
            $this->smtpServer->sendCommand('To: '.$this->getToStr());
            $this->smtpServer->sendCommand('CC: '.$this->getCCStr());
            $this->smtpServer->sendCommand('BCC: '.$this->getBCCStr());
            $this->smtpServer->sendCommand('Date:'.date('r (T)'));
            $this->smtpServer->sendCommand('Subject:'.'=?UTF-8?B?'.base64_encode($this->getSubject()).'?=');
            $this->smtpServer->sendCommand('MIME-Version: 1.0');
            $this->smtpServer->sendCommand('Content-Type: multipart/mixed; boundary="'.$this->boundry.'"'.SMTPServer::NL);
            $this->smtpServer->sendCommand('--'.$this->boundry);
            $this->smtpServer->sendCommand('Content-Type: text/html; charset="UTF-8"'.SMTPServer::NL);
            $this->smtpServer->sendCommand($this->_trimControlChars($this->getDocument()->toHTML()));
            $this->_appendAttachments();
            $this->smtpServer->sendCommand(SMTPServer::NL.'.');
            $this->smtpServer->sendCommand('QUIT');
            $this->runAfterSend();
        } else {
            throw new SMTPException('Unable to login to SMTP server: '.$this->smtpServer->getLastResponse(), $this->smtpServer->getLastResponseCode());
        }
    }
    /**
     * Sets the priority of the message.
     * 
     * @param int $priority The priority of the message. -1 for non-urgent, 0 
     * for normal and 1 for urgent. If the passed value is greater than 1, 
     * then 1 will be used. If the passed value is less than -1, then -1 is 
     * used. Other than that, 0 will be used.
     * 
     * @since 2.0
     */
    public function setPriority(int $priority) {
        $asInt = intval($priority);

        if ($asInt <= -1) {
            $this->priority = -1;
        } else if ($asInt >= 1) {
            $this->priority = 1;
        } else {
            $this->priority = 0;
        }
    }
    /**
     * Sets the subject of the message.
     * 
     * @param string $subject Email subject.
     * 
     * @since 2.0
     */
    public function setSubject(string $subject) {
        $trimmed = $this->_trimControlChars($subject);

        if (strlen($trimmed) > 0) {
            $this->subject = $trimmed;
        }
    }
    /**
     * Sets the display language of the email.
     * 
     * The length of the given string must be 2 characters in order to set the 
     * language code.
     * 
     * @param string $lang a two characters language code such as AR or EN. Default 
     * value is 'EN'.
     * 
     */
    public function setLang(string $langCode = 'EN') : bool {
        $langU = strtoupper(trim($langCode));

        if (strlen($langU) == 2) {
            $this->contentLang = $langU;
            $this->getDocument()->setLanguage($langU);
            
            return true;
        }
        
        return false;
    }
    private function _addAddress(string $address, string $name = null, string $type = 'to') {
        $nameTrimmed = $this->_trimControlChars(str_replace('<', '', str_replace('>', '', $name)));
        $addressTrimmed = $this->_trimControlChars(str_replace('<', '', str_replace('>', '', $address)));

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
     * @since 1.3
     */
    private function _appendAttachments() {
        $atts = $this->getAttachments();
        
        if (count($atts) != 0) {
            foreach ($atts as $fileObj) {
                $fileObj->read();
                $contentChunk = chunk_split($fileObj->getRawData(true));
                $this->smtpServer->sendCommand('--'.$this->boundry);
                $this->smtpServer->sendCommand('Content-Type: '.$fileObj->getMIME().'; name="'.$fileObj->getName().'"');
                $this->smtpServer->sendCommand('Content-Transfer-Encoding: base64');
                $this->smtpServer->sendCommand('Content-Disposition: attachment; filename="'.$fileObj->getName().'"'.SMTPServer::NL);
                $this->smtpServer->sendCommand($contentChunk);
            }
            $this->smtpServer->sendCommand('--'.$this->boundry.'--'.SMTPServer::NL);
        }
    }
    private function _getReceiversStr($type) {
        $arr = [];

        foreach ($this->receiversArr[$type] as $address => $name) {
            $arr[] = '=?UTF-8?B?'.base64_encode($name).'?='.' <'.$address.'>';
        }

        return implode(',', $arr);
    }
    private function _priorityCommand() {
        $priorityAsInt = $this->getPriority();
        $priorityHeaderVal = self::PRIORITIES[$priorityAsInt];

        if ($priorityAsInt == -1) {
            $importanceHeaderVal = 'low';
        } else {
            if ($priorityAsInt == 1) {
                $importanceHeaderVal = 'High';
            } else {
                $importanceHeaderVal = 'normal';
            }
        }
        $this->smtpServer->sendCommand('Priority: '.$priorityHeaderVal);

        return $importanceHeaderVal;
    }
    private function _receiversCommand($type) {
        foreach ($this->receiversArr[$type] as $address => $name) {
            $this->smtpServer->sendCommand('RCPT TO: <'.$address.'>');
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
    private function _trimControlChars($str) {
        return trim($str, "\x00..\x20");
    }
    /**
     * Returns the document that is associated with the page.
     * 
     * @return HTMLDoc An object of type 'HTMLDoc'.
     * 
     * @since 1.0.5
     */
    public function getDocument() {
        return $this->document;
    }
}
