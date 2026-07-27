<?php
namespace WebFiori\Mail;

/**
 * A class that represents SMTP account which is used to connect to SMTP server.
 *
 * @author Ibrahim
 * 
 */
class SMTPAccount {
    /**
     * OAuth access token for authentication.
     * 
     * @var string|null
     */
    private $accessToken;
    /**
     * Whether to allow self-signed SSL/TLS certificates.
     *
     * @var bool
     */
    private bool $allowSelfSigned = false;
    /**
     * Maximum number of connection retry attempts.
     *
     * @var int
     */
    private int $maxRetries = 3;
    /**
     * Base delay in seconds between retry attempts (doubles each attempt).
     *
     * @var int
     */
    private int $retryBaseDelay = 1;
    /**
     * Whether to verify SSL/TLS peer certificate.
     *
     * @var bool
     */
    private bool $verifySsl = true;
    /**
     * The name of SMTP account.
     * 
     * @var string 
     * 
     */
    private $accName;
    /**    /**
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
     * <li><b>access-token</b>: OAuth access token for authentication.</li>
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

        if (isset($options[AccountOption::ACCESS_TOKEN])) {
            $this->setAccessToken($options[AccountOption::ACCESS_TOKEN]);
        }

        if (isset($options[AccountOption::VERIFY_SSL])) {
            $this->setVerifySsl((bool) $options[AccountOption::VERIFY_SSL]);
        }

        if (isset($options[AccountOption::ALLOW_SELF_SIGNED])) {
            $this->setAllowSelfSigned((bool) $options[AccountOption::ALLOW_SELF_SIGNED]);
        }

        if (isset($options[AccountOption::MAX_RETRIES])) {
            $this->setMaxRetries((int) $options[AccountOption::MAX_RETRIES]);
        }

        if (isset($options[AccountOption::RETRY_DELAY])) {
            $this->setRetryBaseDelay((int) $options[AccountOption::RETRY_DELAY]);
        }
    }
    /**
     * Returns the OAuth access token.
     * 
     * @return string|null The OAuth access token or null if not set.
     */
    public function getAccessToken(): ?string {
        return $this->accessToken;
    }
    /**
     * Checks whether self-signed SSL/TLS certificates are allowed.
     *
     * @return bool True if self-signed certificates are allowed, false otherwise.
     */
    public function isAllowSelfSigned(): bool {
        return $this->allowSelfSigned;
    }
    /**
     * Checks whether SSL/TLS peer verification is enabled.
     *
     * @return bool True if SSL peer verification is enabled (default), false otherwise.
     */
    public function isVerifySsl(): bool {
        return $this->verifySsl;
    }
    /**
     * Returns the maximum number of connection retry attempts.
     *
     * @return int
     */
    public function getMaxRetries(): int {
        return $this->maxRetries;
    }
    /**
     * Returns the base delay in seconds between connection retry attempts.
     *
     * @return int
     */
    public function getRetryBaseDelay(): int {
        return $this->retryBaseDelay;
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
     * Returns the address of the sender.
     * 
     * @return string The address of the sender.
     */
    public function getAddress() : string {
        return $this->address;
    }
    /**
     * Returns the password of the email account.
     * 
     * @return string The password of the email account.
     */
    public function getPassword() : string {
        return $this->password;
    }
    /**
     * Returns SMTP server port number.
     * 
     * @return int SMTP server port number. The default value is 465.
     */
    public function getPort() : int {
        return $this->port;
    }
    /**
     * Returns the name of the sender.
     * 
     * @return string The name of the sender.
     */
    public function getSenderName() : string {
        return $this->name;
    }
    /**
     * Returns the address of SMTP server.
     * 
     * @return string The address of SMTP server.
     */
    public function getServerAddress() : string {
        return $this->emailServerAddress;
    }
    /**
     * Returns the username of the email account.
     * 
     * @return string The username of the email account.
     */
    public function getUsername() : string {
        return $this->userName;
    }
    /**
     * Sets the OAuth access token.
     * 
     * @param string|null $token The OAuth access token or null to clear it.
     */
    public function setAccessToken(?string $token): void {
        $this->accessToken = $token;
    }
    /**
     * Sets whether to allow self-signed SSL/TLS certificates.
     *
     * When true, connections to servers with self-signed certificates succeed
     * even without a trusted CA chain. Peer and hostname verification still
     * apply unless disabled via setVerifySsl(false).
     *
     * @param bool $allow True to allow self-signed certificates, false otherwise.
     */
    public function setAllowSelfSigned(bool $allow): void {
        $this->allowSelfSigned = $allow;
    }
    /**
     * Sets the maximum number of times to retry a failed connection.
     *
     * Must be zero or greater. Zero means no retries (fail immediately).
     *
     * @param int $retries Number of retry attempts.
     */
    public function setMaxRetries(int $retries): void {
        if ($retries >= 0) {
            $this->maxRetries = $retries;
        }
    }
    /**
     * Sets the base delay in seconds between connection retry attempts.
     *
     * The actual delay doubles on each attempt (exponential backoff).
     * Must be one or greater.
     *
     * @param int $seconds Base delay in seconds.
     */
    public function setRetryBaseDelay(int $seconds): void {
        if ($seconds >= 1) {
            $this->retryBaseDelay = $seconds;
        }
    }
    /**
     * Sets whether to verify the SSL/TLS peer certificate.
     *
     * When set to false, no certificate validation is performed and the
     * connection is vulnerable to man-in-the-middle attacks. Only disable
     * in controlled environments where SSL verification is not possible.
     *
     * @param bool $verify True to enable verification (default), false to disable.
     */
    public function setVerifySsl(bool $verify): void {
        $this->verifySsl = $verify;
    }
    /**
     * Sets the name of the account.
     * 
     * @param string $name The name of the account.
     */
    public function setAccountName(string $name) {
        $this->accName = $name;
    }
    /**
     * Sets the address of the sender.
     * 
     * @param string $address The address of the sender.
     */
    public function setAddress(string $address) {
        $this->address = trim($address);
    }
    /**
     * Sets the password of the email account.
     * 
     * @param string $pass The password of the email account.
     */
    public function setPassword(string $pass) {
        $this->password = $pass;
    }
    /**
     * Sets SMTP server port number.
     * 
     * @param int $port SMTP server port number.
     */
    public function setPort(int $port) {
        $this->port = $port;
    }
    /**
     * Sets the name of the sender.
     * 
     * @param string $name The name of the sender.
     */
    public function setSenderName(string $name) {
        $this->name = trim($name);
    }
    /**
     * Sets the address of SMTP server.
     * 
     * @param string $addr The address of SMTP server.
     */
    public function setServerAddress(string $addr) {
        $this->emailServerAddress = trim($addr);
    }
    /**
     * Sets the username of the email account.
     * 
     * @param string $u The username of the email account.
     */
    public function setUsername(string $u) {
        $this->userName = trim($u);
    }
}
