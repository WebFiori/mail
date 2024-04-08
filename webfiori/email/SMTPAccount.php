<?php
namespace webfiori\email;

/**
 * A class that represents SMTP account which is used to connect to SMTP server.
 *
 * @author Ibrahim
 * 
 * @version 1.0.3
 */
class SMTPAccount {
    /**
     * The name of SMTP account.
     * 
     * @var string 
     * 
     */
    private $accName; 
    /**
     * Email address.
     * 
     * @var string 
     */
    private $address;
    /**
     * Server address of the email account.
     * 
     * @var string
     * 
     */
    private $emailServerAddress;
    /**
     * The name of the email account.
     * 
     * @var string
     * 
     */
    private $name;
    /**
     * The password of the user account.
     * 
     * @var string
     * 
     */
    private $password;
    /**
     * The port number that is used to access the email server.
     * 
     * @var int
     * 
     */
    private $port;
    /**
     * The username that is used to log-in.
     * 
     * @var string
     * 
     */
    private $userName;
    /**
     * Creates new instance of the class.
     * 
     * @param array $options An optional array that contains connection info. The array 
     * can have the following indices:
     * <ul>
     * <li><b>port</b>: SMTP server port address. usually 25 or 465.</li>
     * <li><b>server-address</b>: SMTP server address.</li>
     * <li><b>user</b>: The username at which it is used to log in to SMTP server.</li>
     * <li><b>pass</b>: The password of the user</li>
     * <li><b>sender-name</b>: The name of the sender that will appear when the 
     * message is sent.</li>
     * <li><b>sender-address</b>: The address that will appear when the 
     * message is sent. Usually, it is the same as the username.</li>
     * <li><b>account-name</b>: A unique name for the account. Used when creating 
     * new email message. If not provided, 'sender-name' is used.</li>
     * </ul>
     * 
     */
    public function __construct(array $options = []) {
        if (isset($options[AccountOption::PORT])) {
            $this->setPort($options[AccountOption::PORT]);
        } else {
            $this->setPort(465);
        }

        if (isset($options[AccountOption::USERNAME])) {
            $this->setUsername($options[AccountOption::USERNAME]);
        } else {
            $this->setUsername('');
        }

        if (isset($options[AccountOption::PASSWORD])) {
            $this->setPassword($options[AccountOption::PASSWORD]);
        } else {
            $this->setPassword('');
        }

        if (isset($options[AccountOption::SERVER_ADDRESS])) {
            $this->setServerAddress($options[AccountOption::SERVER_ADDRESS]);
        } else {
            $this->setServerAddress('');
        }

        if (isset($options[AccountOption::SENDER_NAME])) {
            $this->setSenderName($options[AccountOption::SENDER_NAME]);
        } else {
            $this->setSenderName('');
        }

        if (isset($options[AccountOption::SENDER_ADDRESS])) {
            $this->setAddress($options[AccountOption::SENDER_ADDRESS]);
        } else {
            $this->setAddress('');
        }

        if (isset($options[AccountOption::NAME])) {
            $this->setAccountName($options[AccountOption::NAME]);
        } else {
            $this->setAccountName($this->getSenderName());
        }
    }
    /**
     * Returns the name of the account.
     * 
     * The name of the account is used by the class 'EmailMessage' when creating 
     * new instance of the class. Also, the name is used when storing account 
     * information.
     * 
     * @return string A string that represents the name of the account.
     */
    public function getAccountName() : string {
        return $this->accName;
    }
    /**
     * Returns the email address.
     * 
     * @return string The email address which will be used in the header 
     * 'FROM' when sending an email. Default is empty string.
     * 
     */
    public function getAddress() : string {
        return $this->address;
    }
    /**
     * Returns the password of the account that will be used to access SMTP server.
     * 
     * @return string The password of the user account that is used to access email server. 
     * default is empty string.
     * 
     */
    public function getPassword() : string {
        return $this->password;
    }
    /**
     * Returns SMTP server port number.
     * 
     * @return int Default is 465.
     * 
     */
    public function getPort() : int {
        return $this->port;
    }
    /**
     * Returns the name of sender that will be used in the 'FROM' header.
     * 
     * @return string The name of the email sender. Usually this is similar to 
     * email address but can also be a name.
     * 
     */
    public function getSenderName() : string {
        return $this->name;
    }
    /**
     * Returns SMTP server address.
     * 
     * @return string The address of the SMTP server (such as 'mail.example.com'). 
     * 
     */
    public function getServerAddress() : string {
        return $this->emailServerAddress;
    }
    /**
     * Returns the username that is used to access SMTP server.
     * 
     * @return string The username that is used to access email server. Default 
     * is empty string.
     * 
     */
    public function getUsername() : string {
        return $this->userName;
    }
    /**
     * Sets the name of the account.
     * 
     * The name of the account is used by the class 'EmailMessage' when creating 
     * new instance of the class. Also, the name is used when storing the account.
     * 
     * @param string $name The name of the account.
     * 
     */
    public function setAccountName(string $name) {
        $this->accName = $name;
    }
    /**
     * Sets the email address.
     * 
     * @param string $address An email address.
     * 
     */
    public function setAddress(string $address) {
        $this->address = trim($address);
    }
    /**
     * Sets the password of the user account that is used to access email server.
     * 
     * @param string $pass The password of the user account that is used to access email server.
     * 
     */
    public function setPassword(string $pass) {
        $this->password = $pass;
    }
    /**
     * Sets port number of SMTP server.
     * 
     * @param int $port The port number of email server. Common ports are 25, 465
     * and 586.
     * 
     */
    public function setPort(int $port) {
        $this->port = $port;
    }
    /**
     * Sets the name of the email account.
     * 
     * @param string $name The name of the account (such as 'Programming Team'). 
     * The name is used when sending an email message using the given SMTP account. 
     * The name will be used in the header 
     * 'FROM' when sending an email.
     * 
     */
    public function setSenderName(string $name) {
        $this->name = trim($name);
    }
    /**
     * Sets the address of the email server.
     * 
     * @param string $addr The address of the email server (such as 'mail.example.com').
     * 
     */
    public function setServerAddress(string $addr) {
        $this->emailServerAddress = trim($addr);
    }
    /**
     * Sets the username that is used to access email server.
     * 
     * @param string $u The username that is used to access email server.
     * 
     */
    public function setUsername(string $u) {
        $this->userName = trim($u);
    }
}
