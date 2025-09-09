<?php

require '../../vendor/autoload.php';

use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;

// Configure Microsoft OAuth account
$microsoftAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp-mail.outlook.com',
    AccountOption::PORT => 587,
    AccountOption::USERNAME => 'your-email@outlook.com',
    AccountOption::ACCESS_TOKEN => 'your-microsoft-oauth-token',
    AccountOption::SENDER_ADDRESS => 'your-email@outlook.com',
    AccountOption::SENDER_NAME => 'Your Name',
    AccountOption::NAME => 'microsoft-oauth'
]);

// Create email instance
$email = new Email($microsoftAccount);

// Configure email using fluent interface
$email->to('recipient@example.com')
      ->subject('Microsoft OAuth Demo')
      ->priority(1);

// Add structured content
$email->insert('div', ['style' => ['font-family' => 'Arial, sans-serif']])
      ->addChild('h2', ['style' => ['color' => '#0078d4']])
      ->text('Microsoft OAuth Integration');

$content = $email->insert('div');
$content->addChild('p')->text('This email demonstrates OAuth2 integration with Microsoft services.');
$content->addChild('p')->text('Perfect for Office 365 and Outlook.com accounts.');

// Add feature list
$features = $content->addChild('div');
$features->addChild('h3')->text('Key Features:');
$list = $features->addChild('ol');
$list->addChild('li')->text('Secure token-based authentication');
$list->addChild('li')->text('Compatible with Office 365');
$list->addChild('li')->text('Supports multi-tenant applications');
$list->addChild('li')->text('Automatic token refresh capabilities');

// Send email
try {
    $email->send();
    echo "Email sent successfully using Microsoft OAuth!\n";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}
