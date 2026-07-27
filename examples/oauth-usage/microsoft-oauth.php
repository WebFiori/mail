<?php

require '../../vendor/autoload.php';

use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;
use WebFiori\Mail\MicrosoftOAuthProvider;
use WebFiori\Mail\SMTPAccount;

// -------------------------------------------------------------------------
// Step 1: Create the provider with your Azure App Registration credentials.
//
// These values come from:
//   - Azure Portal → Entra ID → App registrations → your app → Overview
//   - SMTP_TENANT_ID  : Directory (tenant) ID
//   - SMTP_CLIENT_ID  : Application (client) ID
//   - SMTP_CLIENT_SECRET: Certificates & secrets → client secret Value
//
// Store them in environment variables, not in source code.
// -------------------------------------------------------------------------
$provider = new MicrosoftOAuthProvider(
    tenantId:     getenv('SMTP_TENANT_ID'),
    clientId:     getenv('SMTP_CLIENT_ID'),
    clientSecret: getenv('SMTP_CLIENT_SECRET')
);

// -------------------------------------------------------------------------
// Step 2: Configure the SMTP account.
//
// The password field is intentionally empty — OAuth replaces it.
// The provider's getToken() is called lazily just before each send,
// so the token is always fresh even in long-running processes.
// -------------------------------------------------------------------------
$account = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.office365.com',
    AccountOption::PORT           => 587,
    AccountOption::USERNAME       => getenv('SMTP_USERNAME'), // the sending mailbox address
    AccountOption::SENDER_ADDRESS => getenv('SMTP_USERNAME'),
    AccountOption::SENDER_NAME    => 'My Application',
    AccountOption::NAME           => 'no-reply',
]);
$account->setTokenProvider($provider);

// -------------------------------------------------------------------------
// Step 3: Compose and send.
// -------------------------------------------------------------------------
$email = new Email($account);
$email->setSubject('Microsoft OAuth Demo');
$email->addTo('recipient@example.com', 'Recipient Name');

$email->insert('h1')->text('Microsoft OAuth Integration');
$email->insert('p')->text('This email was sent using SMTP XOAUTH2 via Microsoft Entra ID.');
$email->insert('p')->text('No password was stored — only a short-lived access token was used.');

try {
    $email->send();
    echo "Email sent successfully!\n";
    echo "Message-ID: " . $email->getMessageId() . "\n";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}

// -------------------------------------------------------------------------
// Token caching across multiple sends
// -------------------------------------------------------------------------
// The provider caches the token in memory. Sending multiple emails in the
// same process reuses the token until it is within 5 minutes of expiry,
// at which point a new one is fetched automatically.

$email2 = new Email($account); // reuses the same $account with $provider
$email2->setSubject('Second email — same token if still valid');
$email2->addTo('another@example.com');
$email2->insert('p')->text('Sent with the cached token from the same provider instance.');

try {
    $email2->send();
    echo "Second email sent successfully!\n";
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
