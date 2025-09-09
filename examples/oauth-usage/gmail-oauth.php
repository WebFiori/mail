<?php

require '../../vendor/autoload.php';

use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;

// Configure Gmail OAuth account
$gmailAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.gmail.com',
    AccountOption::PORT => 587,
    AccountOption::USERNAME => 'your-email@gmail.com',
    AccountOption::ACCESS_TOKEN => 'your-oauth-access-token',
    AccountOption::SENDER_ADDRESS => 'your-email@gmail.com',
    AccountOption::SENDER_NAME => 'Your Name',
    AccountOption::NAME => 'gmail-oauth'
]);

// Create email with OAuth authentication
$email = new Email($gmailAccount);

// Set email details
$email->setSubject('OAuth Authentication Demo');
$email->addTo('recipient@example.com', 'Recipient');

// Add content explaining OAuth
$email->insert('h1')->text('OAuth2 Authentication Success!');
$email->insert('p')->text('This email was sent using OAuth2 authentication instead of traditional username/password.');
$email->insert('p')->text('OAuth provides better security and is the recommended method for modern applications.');

$benefits = $email->insert('ul');
$benefits->addChild('li')->text('Enhanced security with token-based authentication');
$benefits->addChild('li')->text('No need to store passwords in your application');
$benefits->addChild('li')->text('Granular permission control');
$benefits->addChild('li')->text('Token can be revoked without changing password');

// Send the email
try {
    $email->send();
    echo "Email sent successfully using OAuth2!\n";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}
