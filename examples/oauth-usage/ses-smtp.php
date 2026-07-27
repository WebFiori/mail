<?php

require '../../vendor/autoload.php';

use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;
use WebFiori\Mail\SESCredentialHelper;
use WebFiori\Mail\SMTPAccount;

// -------------------------------------------------------------------------
// Amazon SES SMTP — using SESCredentialHelper
//
// SES SMTP does not use OAuth. Instead it uses IAM credentials: the
// Access Key ID is the SMTP username and the SMTP password is derived
// from the Secret Access Key using a specific HMAC-SHA256 algorithm.
//
// SESCredentialHelper::deriveSmtpPassword() performs this derivation so
// you don't have to implement it manually.
//
// Prerequisites:
//   1. Verify your sending domain or address in SES
//   2. Create an IAM user with the AmazonSesSendingAccess policy
//   3. Generate an Access Key for that IAM user
//   4. If your account is in the SES sandbox, verify recipient addresses too
// -------------------------------------------------------------------------

$region = getenv('AWS_REGION') ?: 'us-east-1';
$accessKeyId = getenv('AWS_ACCESS_KEY_ID');      // SMTP username
$secretAccessKey = getenv('AWS_SECRET_ACCESS_KEY');  // used to derive SMTP password

// Derive the SES SMTP password from the IAM Secret Access Key.
// This is stable — the same key + region always produces the same password.
// Re-derive only when you rotate your IAM key.
$smtpPassword = SESCredentialHelper::deriveSmtpPassword($secretAccessKey, $region);

// -------------------------------------------------------------------------
// Configure the SMTP account using standard AccountOption fields.
// No special transport or provider needed — SES SMTP uses AUTH LOGIN.
// -------------------------------------------------------------------------
$account = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => SESCredentialHelper::smtpEndpoint($region),
    AccountOption::PORT => 587,
    AccountOption::USERNAME => $accessKeyId,
    AccountOption::PASSWORD => $smtpPassword,
    AccountOption::SENDER_ADDRESS => 'sender@verified-domain.com',
    AccountOption::SENDER_NAME => 'My Application',
    AccountOption::NAME => 'ses-account',
]);

// -------------------------------------------------------------------------
// Compose and send — exactly the same as any other account.
// -------------------------------------------------------------------------
$email = new Email($account);
$email->setSubject('Amazon SES SMTP Demo');
$email->addTo('recipient@example.com', 'Recipient Name');

$email->insert('h1')->text('Amazon SES Integration');
$email->insert('p')->text('This email was sent via Amazon SES SMTP using IAM credentials.');
$email->insert('p')->text('The SMTP password was derived from the IAM Secret Access Key.');

$info = $email->insert('div', ['style' => 'margin-top:16px;padding:12px;background:#f5f5f5']);
$info->addChild('strong')->text('Sending details:');
$list = $info->addChild('ul');
$list->addChild('li')->text("Region: {$region}");
$list->addChild('li')->text("Endpoint: ".SESCredentialHelper::smtpEndpoint($region));
$list->addChild('li')->text("Access Key ID: {$accessKeyId}");

try {
    $email->send();
    echo "Email sent successfully via SES!\n";
    echo "Message-ID: ".$email->getMessageId()."\n";
} catch (Exception $e) {
    echo "Failed to send email: ".$e->getMessage()."\n";
}

// -------------------------------------------------------------------------
// Storing the derived password
// -------------------------------------------------------------------------
// Since the derived password is deterministic, you can pre-compute it once
// and store it securely (e.g. in an environment variable or secrets manager)
// to avoid the derivation cost on every application boot:
//
//   # Compute once (in a setup script):
//   $stored = SESCredentialHelper::deriveSmtpPassword($secret, $region);
//   // → store $stored as SES_SMTP_PASSWORD env var
//
//   # Use directly in production:
//   AccountOption::PASSWORD => getenv('SES_SMTP_PASSWORD')
