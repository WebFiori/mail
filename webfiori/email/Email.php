<?php
namespace webfiori\email;

use TypeError;
use webfiori\email\exceptions\SMTPException;
use webfiori\file\exceptions\FileException;
use webfiori\file\File;
use webfiori\ui\exceptions\InvalidNodeNameException;
use webfiori\ui\exceptions\TemplateNotFoundException;
use webfiori\ui\HTMLDoc;
use webfiori\ui\HTMLNode;
/**
 * A class that can be used to write HTML formatted Email messages.
 *
 * @author Ibrahim
 */
class Email {
    /**
     * A constant that colds the possible values for the header 'Priority'. 
     * 
     * @see https://tools.ietf.org/html/rfc4021#page-33
     * 
     */
    const PRIORITIES = [
        -1 => 'non-urgent',
        0 => 'normal',
        1 => 'urgent'
    ];
    private $isSent;
    /**
     * Checks if the method Email::send() was called or not.
     * 
     * @return bool If it was called, the method will return true. False otherwise.
     */
    public function isSent() : bool {
        return $this->isSent;
    }
    private $afterSendPool;
    /**
     * An array that contains an objects of type 'File' or 
     * file path. 
     * 
     * @var array 
     * 
     */
    private $attachments;
    /**
     * An array that holds callbacks which will get executed before sending
     * the message.
     * 
     * @var array
     * 
     */
    private $beforeSendPool;
    /**
     * A boundary variable used to separate email message parts.
     * 
     * @var string
     * 
     */
    private $boundry;
    /**
     *
     * @var HTMLDoc 
     * 
     */
    private $document;
    private $inReplyTo;

    private $log;
    private $mode;
    private $modeConfig;
    private $priority;
    /**
     * 
     * @var array
     * 
     */
    private $receiversArr;
    /**
     * SMTP account that will be used to send the message.
     * 
     * @var SMTPAccount
     * 
     */
    private $smtpAcc;
    /**
     * 
     * @var SMTPServer|null
     * 
     */
    private $smtpServer;
    /**
     * The subject of the email message.
     * 
     * @var string 
     * 
     */
    private $subject;
    /**
     * Creates new instance of the class.
     * 
     * @param SMTPAccount $sendAccount The SMTP connection that will be
     * used to send the message.
     * 
     */
    public function __construct(SMTPAccount|null $sendAccount = null) {
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
        $this->isSent = false;
        $this->document = new HTMLDoc();
        $this->mode = SendMode::PROD;
        $this->modeConfig = [];
        $this->addBeforeSend(function (Email $email) {
            $email->isSent = true;
        }, [$this]);
        if ($sendAccount !== null) {
            $this->setSMTPAccount($sendAccount);
        }
    }
    /**
     * Returns a string representation of the email.
     * 
     * The method will return HTML code which represent the message.
     * 
     * @return string
     */
    public function __toString() {
        return $this->getDocument()->toHTML(true);
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
     * @return Email The method will return same instance at which the method is
     * called on.
     * 
     */
    public function addAfterSend(callable $callback, array $extraParams = []) : Email {
        $this->afterSendPool[] = [
            'func' => $callback,
            'params' => array_merge([$this], $extraParams),
            'executed' => false
        ];

        return $this;
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
     */
    public function addBCC(string $address, string|null $name = null): bool {
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
     * @return Email The method will return same instance at which the method is
     * called on.
     */
    public function addBeforeSend(callable $callback, array $extraParams = []) : Email {
        $this->beforeSendPool[] = [
            'func' => $callback,
            'params' => array_merge([$this], $extraParams),
            'executed' => false
        ];

        return $this;
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
     */
    public function addCC(string $address, string|null $name = null) : bool {
        return $this->addAddressHelper($address, $name, 'cc');
    }
    /**
     * Adds multiple recipients as a one batch.
     * 
     * @param array $addresses This can be an indexed array or associative array.
     * In case of indexed, it can have the email addresses of the recipients.
     * In case of associative, the keys are email addresses of the recipients
     * and the values are their names.
     * 
     * @param string $recipientsType The type of the recipients. Can only be
     * one of the following values: 'to', 'cc' or 'bcc'. Default is 'to'.
     * 
     * @return Email The method will return same instance at which the method is
     * called on.
     */
    public function addRecipients(array $addresses, string $recipientsType = 'to') : Email {
        $typeCorrected = strtolower(trim($recipientsType));

        if (in_array($typeCorrected, ['to', 'cc', 'bcc'])) {
            foreach ($addresses as $address => $name) {
                if (gettype($address) == 'integer') {
                    $address = $name;
                }
                $this->addAddressHelper($address, $name, $typeCorrected);
            }
        }

        return $this;
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
     */
    public function addTo(string $address, string|null $name = null) : bool {
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
     */
    public function getChildByID(string $id) {
        return $this->getDocument()->getChildByID($id);
    }
    /**
     * Returns the document that is associated with the page.
     * 
     * @return HTMLDoc An object of type 'HTMLDoc'.
     * 
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
     */
    public function getLog() : array {
        return $this->getSMTPServer()->getLog();
    }
    /**
     * Returns the mode at which the message will use when the method 'send' is called.
     * 
     * @return string The method will return one of 3 values:
     * 
     * <ul>
     * <li><b>SendMode::PROD</b>: This is the default mode. The message will be 
     * sent to its recipients.</li>
     * <li><b>SendMode::TEST_SEND</b>: This mode indicates that the message will be
     * sent to a set of specified addresses by <b>$config</b> with meta-data
     * of the message. Used to mimic actual process of sending a message.</li>
     * <li><b>SendMode::TEST_STORE</b>: The message including its meta-data will
     * be stored as HTML web page in specific path specified by <b>$confing</b>.</li>
     * </ul>
     */
    public function getMode() : string {
        return $this->mode;
    }
    /**
     * Returns an array that holds the configuration of send mode of the message.
     * 
     * Possible indices of the are:
     * <ul>
     * <li><b>store-path</b>: Represents the location at which the
     * message will be stored at when the mode <b>SendMode::TEST_STORE</b> is used.</li>
     * <li><b>send-addresses</b>: Represents an array that holds
     * the addresses at which the message will be sent to when the 
     * mode <b>SendMode::TEST_SEND</b> is used.</li>
     * </ul>
     * 
     * @return array An associative array.
     */
    public function getModeConfig() : array {
        return $this->modeConfig;
    }
    /**
     * Returns the priority of the message.
     * 
     * @return int The priority of the message. -1 for non-urgent, 0 
     * for normal and 1 for urgent. Default value is 0.
     * 
     */
    public function getPriority() : int {
        return $this->priority;
    }
    /**
     * Returns SMTP connection information which is used to connect to SMTP server.
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
     * to see if the message was transferred or not. Note that the 
     * connection to the server will only be established once the 
     * method 'Email::send()'.
     * 
     * @return SMTPServer An instance which represents SMTP server.
     * 
     */
    public function getSMTPServer() : SMTPServer {
        return $this->smtpServer;
    }
    /**
     * Returns the subject of the email.
     * 
     * @return string The subject of the email. Default return value is 
     * 'Hello Email Message'.
     * 
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
     */
    public function insert($node, string|null $parentNodeId = null) {
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
     * Loads a template and insert its content to the body of the message.
     * 
     * @param string $path The absolute path to the template. It can be a PHP
     * file or HTML file.
     * 
     * @param array $parameters An optional associative array of parameters to be passed to
     * the template. The key will be always acting as parameter name. 
     * In case of HTML, the parameters can be slots enclosed
     * between two curly braces (e.g '{{NAME}}'). In case of PHP template,
     * the associative array will be converted to variables that can be used
     * within the template. 
     * 
     * @throws TemplateNotFoundException If no template file was found in provided
     * path.
     * 
     * @return Email The method will return same instance at which the method is
     * called on.
     */
    public function insertFromTemplate(string $path, array $parameters = []) : Email {
        $content = HTMLNode::fromFile($path, $parameters);

        if (gettype($content) == 'array') {
            foreach ($content as $node) {
                $this->insert($node);
            }
        } else {
            $this->insert($content);
        }

        return $this;
    }
    /**
     * Execute all the callbacks which are set to execute after sending the
     * message.
     */
    public function invokeAfterSend() {
        foreach ($this->afterSendPool as &$callArr) {
            if (!$callArr['executed']) {
                call_user_func_array($callArr['func'], $callArr['params']);
                $callArr['executed'] = true;
            }
        }
    }
    /**
     * Execute all the callbacks which are set to execute before sending the
     * message.
     */
    public function invokeBeforeSend() {
        foreach ($this->beforeSendPool as &$callArr) {
            if (!$callArr['executed']) {
                call_user_func_array($callArr['func'], $callArr['params']);
                $callArr['executed'] = true;
            }
        }
    }
    public function rcptCount() : int {
        return count($this->getCC()) + count($this->getBCC()) + count($this->getTo());
    }
    /**
     * Removes all email addresses of the users who where set to recive the email.
     */
    public function removeAllRecipients() {
        $this->receiversArr = [
            'cc' => [],
            'bcc' => [],
            'to' => []
        ];
    }
    /**
     * Sends the message.
     * 
     * @throws SMTPException
     */
    public function send() {
        
        if ($this->isSent()) {
            throw new SMTPException('Message was already sent.');
        }
        $this->invokeBeforeSend();

        $sendMode = $this->getMode();

        if ($sendMode == SendMode::TEST_STORE) {
            $config = $this->getModeConfig();

            if (!isset($config['store-path'])) {
                throw new FileException('Store path is not set for mode SendMode::TEST_STORE.');
            }
            $path = $config['store-path'];

            if (!File::isDirectory($path)) {
                throw new FileException('Store path does not exist: \''.$path.'\'');
            }
            $this->setupBeoreTesting();
            $this->storeEmail($path);
            $this->invokeAfterSend();

            return;
        } else if ($sendMode == SendMode::TEST_SEND) {
            $config = $this->getModeConfig();
            
            if (!isset($config['send-addresses'])) {
                throw new SMTPException('Recipients are not set for mode SendMode::TEST_SEND.');
            }
            $rcpt = $config['send-addresses'];
            
            if (gettype($rcpt) == 'string') {
                $rcpt = explode(';', $rcpt);
            }
            $this->setupBeoreTesting();
            $this->removeAllRecipients();

            foreach ($rcpt as $addr) {
                $trimmed = trim($addr);

                if (strlen($trimmed) != 0) {
                    $this->addTo($trimmed);
                }
            }
        } 

        $acc = $this->getSMTPAccount();
        $server = $this->getSMTPServer();

        if ($this->rcptCount() == 0) {
            throw new SMTPException('No message recipients.');
        }

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
            $this->invokeAfterSend();
        } else {
            throw new SMTPException('Unable to login to SMTP server: '.$server->getLastResponse(), $server->getLastResponseCode(), $server->getLog());
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
     * @return Email The method will return same instance at which the method is
     * called on.
     */
    public function setLang(string $langCode = 'EN') : Email {
        $langU = strtoupper(trim($langCode));

        if (strlen($langU) == 2) {
            $this->getDocument()->setLanguage($langU);
        }

        return $this;
    }
    /**
     * Sets the mode at which the message will use when the send method is called.
     * 
     * @param string $mode This can be one of 3 values:
     * <ul>
     * <li><b>SendMode::PROD</b>: This is the default mode. The message will be 
     * sent to its recipients.</li>
     * <li><b>SendMode::TEST_SEND</b>: This mode indicates that the message will be
     * sent to a set of specified addresses by <b>$config</b> with meta-data
     * of the message. Used to mimic actual process of sending a message.</li>
     * <li><b>SendMode::TEST_STORE</b>: The message including its meta-data will
     * be stored as HTML web page in specific path specified by <b>$confing</b>.</li>
     * </ul>
     * 
     * @param array $config An array that holds send option configuration.
     * The array can have following indices:
     * <ul>
     * <li><b>store-path</b>: Represents the location at which the
     * message will be stored at when the mode <b>SendMode::TEST_STORE</b> is used.</li>
     * <li><b>send-addresses</b>: Represents an array that holds
     * the addresses at which the message will be sent to when the 
     * mode <b>SendMode::TEST_SEND</b> is used.</li>
     * </ul>
     * 
     * @return bool If the mode successfully updated, true is returned.
     * Other than that, false is returned.
     */
    public function setMode(string $mode, array $config = []) : bool {
        $trimmed = strtolower(trim($mode));

        if ($mode == SendMode::PROD || $mode == SendMode::TEST_SEND || $mode == SendMode::TEST_STORE) {
            $this->mode = $trimmed;
            $this->modeConfig = $config;

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
     * @return Email The method will return same instance at which the method is
     * called on.
     * 
     */
    public function setPriority(int $messagePriority) : Email {
        if ($messagePriority <= -1) {
            $this->priority = -1;
        } else if ($messagePriority >= 1) {
            $this->priority = 1;
        } else {
            $this->priority = 0;
        }

        return $this;
    }

    /**
     * Sets SMTP account that will be used by SMTP server.
     *
     * @param SMTPAccount $account An account that holds connection information.
     *
     * @return Email The method will return same instance at which the method is
     * called on.
     */
    public function setSMTPAccount(SMTPAccount $account) : Email {
        $this->smtpAcc = $account;
        $this->smtpServer = new SMTPServer($account->getServerAddress(), $account->getPort());

        return $this;
    }
    /**
     * Sets the subject of the message.
     * 
     * @param string $subject Email subject.
     * 
     * @return Email The method will return same instance at which the method is
     * called on.
     * 
     */
    public function setSubject(string $subject) : Email {
        $trimmed = $this->trimControlChars($subject);

        if (strlen($trimmed) > 0) {
            $this->subject = $trimmed;
            $this->getDocument()->getHeadNode()->setPageTitle($trimmed);
        }

        return $this;
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
        $name = str_replace(':?\\//*<>|', '', $this->getSubject());

        $file = new File($folderPath.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.date('Y-m-d H-i-s').'.html');
        $file->setRawData($this->getDocument()->toHTML(true).'');
        $file->write(false, true);
    }
    private function addAddressHelper(string $address, string|null $name = null, string $type = 'to') : bool {
        if ($name === null || strlen(trim($name)) == 0) {
            $name = $address;
        }
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
        try {
            $this->getSMTPServer()->sendCommand('Priority: '.$priorityHeaderVal);
        } catch (TypeError $ex) {
        }

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
    private function setupBeoreTesting() {
        try {
            $acc = $this->getSMTPAccount();
        } catch (TypeError $ex) {
            $acc = null;
        }

        $headersTable = new HeadersTable();
        $headersTable->addHeader('Importance', $this->priorityCommandHelper());

        if ($acc !== null) {
            $headersTable->addHeader('From', $acc->getSenderName().' <'.$acc->getAddress().'>');
        } else {
            $headersTable->addHeader('From', ' <NOT SPECIFIED>');
        }
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
        $this->invokeAfterSend();
        $this->getDocument()->getBody()->insert(new HTMLNode('hr'), 0);
        $this->getDocument()->getBody()->insert(new HTMLNode('p'), 0)->text('----Actual Email Starts After This Line----')->setStyle([
            'font-weight' => '600'
        ]);
        $this->getDocument()->getBody()->insert($headersTable, 0);
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
        $trimmed = trim($str, "\x00..\x20");
        //Removes any invalid line feed.
        return preg_replace("/(\s*[\r\n]+\s*|\s+)/", ' ', $trimmed);
    }
}
