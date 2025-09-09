<?php

require '../../vendor/autoload.php';

use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;
use WebFiori\Mail\SendMode;

// Configure SMTP account
$smtpAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.gmail.com',
    AccountOption::PORT => 587,
    AccountOption::USERNAME => 'your-email@gmail.com',
    AccountOption::PASSWORD => 'your-app-password',
    AccountOption::SENDER_ADDRESS => 'your-email@gmail.com',
    AccountOption::SENDER_NAME => 'Your Name',
    AccountOption::NAME => 'gmail-account'
]);

// Create email instance
$email = new Email($smtpAccount);

// Set email details for production recipients
$email->setSubject('Test Send Mode Demo');
$email->addTo('customer@example.com', 'Customer');
$email->addTo('user@example.com', 'User');
$email->addCC('manager@example.com', 'Manager');

// Add content
$email->insert('h1')->text('Test Send Mode');
$email->insert('p')->text('This email was configured to send to production recipients, but test mode redirects it to test addresses.');

$info = $email->insert('div', ['style' => ['background' => '#f0f8ff', 'padding' => '10px', 'border-left' => '4px solid #007acc']]);
$info->addChild('h3')->text('Original Recipients:');
$originalList = $info->addChild('ul');
$originalList->addChild('li')->text('TO: customer@example.com, user@example.com');
$originalList->addChild('li')->text('CC: manager@example.com');

$info->addChild('p')->text('In TEST_SEND mode, these emails will be redirected to the test addresses specified below.');

// Set test send mode with specific test addresses
$email->setMode(SendMode::TEST_SEND, [
    'send-addresses' => [
        'developer@yourcompany.com',
        'qa-team@yourcompany.com'
    ]
]);

// Add information about test mode
$testInfo = $email->insert('div', ['style' => ['background' => '#fff3cd', 'padding' => '10px', 'border-left' => '4px solid #ffc107']]);
$testInfo->addChild('h3')->text('Test Mode Active:');
$testList = $testInfo->addChild('ul');
$testList->addChild('li')->text('Actual recipients: developer@yourcompany.com, qa-team@yourcompany.com');
$testList->addChild('li')->text('Original recipients are preserved in email content');
$testList->addChild('li')->text('Safe for testing without bothering real users');

// Send the email to test addresses
try {
    $email->send();
    echo "Email sent successfully in test mode!\n";
    echo "Sent to test addresses instead of production recipients.\n";
    
    // Show where the email actually went
    echo "\nTest addresses used:\n";
    echo "- developer@yourcompany.com\n";
    echo "- qa-team@yourcompany.com\n";
    
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}

// Display current mode
echo "\nCurrent mode: ";
switch ($email->getMode()) {
    case SendMode::TEST_SEND:
        echo "TEST_SEND - Redirecting to test addresses\n";
        break;
    case SendMode::TEST_STORE:
        echo "TEST_STORE - Storing as HTML files\n";
        break;
    case SendMode::LIVE:
        echo "LIVE - Sending to actual recipients\n";
        break;
}
