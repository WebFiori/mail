<?php

require '../../vendor/autoload.php';

use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;

// -------------------------------------------------------------------------
// Example 1: Default (secure) — SSL peer verification enabled (recommended)
// -------------------------------------------------------------------------
// By default, verify_peer and verify_peer_name are true and
// allow_self_signed is false. This is the correct setting for any
// publicly trusted SMTP server (Gmail, Outlook, etc.).
$secureAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.gmail.com',
    AccountOption::PORT           => 587,
    AccountOption::USERNAME       => 'your-email@gmail.com',
    AccountOption::PASSWORD       => 'your-app-password',
    AccountOption::SENDER_ADDRESS => 'your-email@gmail.com',
    AccountOption::SENDER_NAME    => 'Your Name',
    // AccountOption::VERIFY_SSL is true by default — no need to set it
]);

echo "verify_ssl:        " . ($secureAccount->isVerifySsl() ? 'true' : 'false') . "\n";
echo "allow_self_signed: " . ($secureAccount->isAllowSelfSigned() ? 'true' : 'false') . "\n\n";

// -------------------------------------------------------------------------
// Example 2: Internal server with self-signed certificate
// -------------------------------------------------------------------------
// For private/internal SMTP servers that use a self-signed certificate,
// enable allow_self_signed while keeping verify_peer on. The certificate
// still has to be structurally valid and the hostname must match.
$internalAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS  => 'mail.internal.example.com',
    AccountOption::PORT            => 587,
    AccountOption::USERNAME        => 'no-reply@internal.example.com',
    AccountOption::PASSWORD        => 'your-password',
    AccountOption::SENDER_ADDRESS  => 'no-reply@internal.example.com',
    AccountOption::SENDER_NAME     => 'Internal Mailer',
    AccountOption::ALLOW_SELF_SIGNED => true,  // allow self-signed cert
    // VERIFY_SSL remains true — hostname and cert structure are still checked
]);

echo "verify_ssl:        " . ($internalAccount->isVerifySsl() ? 'true' : 'false') . "\n";
echo "allow_self_signed: " . ($internalAccount->isAllowSelfSigned() ? 'true' : 'false') . "\n\n";

// You can also change these settings after construction:
$internalAccount->setAllowSelfSigned(false); // revert if needed

// -------------------------------------------------------------------------
// Example 3: Disable verification entirely (NOT recommended for production)
// -------------------------------------------------------------------------
// Only use this in isolated environments where SSL verification is
// not possible at all. This makes the connection vulnerable to
// man-in-the-middle attacks.
$insecureAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'localhost',
    AccountOption::PORT           => 1025,
    AccountOption::USERNAME       => 'test@localhost',
    AccountOption::PASSWORD       => '',
    AccountOption::SENDER_ADDRESS => 'test@localhost',
    AccountOption::SENDER_NAME    => 'Local Test',
    AccountOption::VERIFY_SSL     => false,  // disables all certificate verification
]);

echo "verify_ssl:        " . ($insecureAccount->isVerifySsl() ? 'true' : 'false') . "\n";
echo "allow_self_signed: " . ($insecureAccount->isAllowSelfSigned() ? 'true' : 'false') . "\n\n";

// -------------------------------------------------------------------------
// Example 4: Custom retry and timeout configuration
// -------------------------------------------------------------------------
// By default, failed connections are retried up to 3 times with exponential
// backoff starting at 1 second (1s, 2s, 4s). You can tune this per-account.
$reliableAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.gmail.com',
    AccountOption::PORT           => 587,
    AccountOption::USERNAME       => 'your-email@gmail.com',
    AccountOption::PASSWORD       => 'your-app-password',
    AccountOption::SENDER_ADDRESS => 'your-email@gmail.com',
    AccountOption::SENDER_NAME    => 'Your Name',
    AccountOption::MAX_RETRIES    => 5,   // retry up to 5 times
    AccountOption::RETRY_DELAY    => 2,   // backoff: 2s, 4s, 8s, 16s, 32s
]);

echo "max_retries:  " . $reliableAccount->getMaxRetries() . "\n";
echo "retry_delay:  " . $reliableAccount->getRetryBaseDelay() . "s base\n\n";

// You can also disable retries entirely:
$noRetryAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.example.com',
    AccountOption::PORT           => 587,
    AccountOption::USERNAME       => 'user@example.com',
    AccountOption::PASSWORD       => 'secret',
    AccountOption::SENDER_ADDRESS => 'user@example.com',
    AccountOption::SENDER_NAME    => 'Example',
    AccountOption::MAX_RETRIES    => 0,  // fail immediately, no retries
]);

echo "max_retries:  " . $noRetryAccount->getMaxRetries() . " (disabled)\n\n";

// -------------------------------------------------------------------------
// Sending with any of the accounts above works the same way
// -------------------------------------------------------------------------
$email = new Email($secureAccount);
$email->setSubject('SSL Configuration Demo');
$email->addTo('recipient@example.com', 'Recipient');
$email->insert('p')->text('This email was sent with full SSL peer verification enabled.');

try {
    $email->send();
    echo "Email sent successfully.\n";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}
$email = new Email($secureAccount);
$email->setSubject('SSL Configuration Demo');
$email->addTo('recipient@example.com', 'Recipient');
$email->insert('p')->text('This email was sent with full SSL peer verification enabled.');

try {
    $email->send();
    echo "Email sent successfully.\n";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}
