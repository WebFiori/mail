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

// Set email details
$email->setSubject('Test Mode Demo - Email Storage');
$email->addTo('production-user@example.com', 'Production User');
$email->addCC('manager@example.com', 'Manager');

// Add content
$email->insert('h1')->text('Test Mode Demonstration');
$email->insert('p')->text('This email demonstrates the test storage mode of WebFiori Mailer.');
$email->insert('p')->text('Instead of sending to actual recipients, this email will be stored as an HTML file for preview.');

$features = $email->insert('div');
$features->addChild('h3')->text('Test Mode Benefits:');
$list = $features->addChild('ul');
$list->addChild('li')->text('Preview email content without sending');
$list->addChild('li')->text('Test email layouts and styling');
$list->addChild('li')->text('Debug email content safely');
$list->addChild('li')->text('No risk of sending test emails to real users');

// Set test mode to store emails as HTML files
$storageDir = __DIR__ . '/email-previews';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$email->setMode(SendMode::TEST_STORE, [
    'store-path' => $storageDir
]);

// "Send" the email (will be stored instead)
try {
    $email->send();
    echo "Email stored successfully in test mode!\n";
    echo "Check the email-previews directory for the HTML file.\n";
    echo "Storage directory: " . $storageDir . "\n";
} catch (Exception $e) {
    echo "Failed to store email: " . $e->getMessage() . "\n";
}

// Display mode information
echo "\nCurrent sending mode: ";
switch ($email->getMode()) {
    case SendMode::TEST_STORE:
        echo "TEST_STORE (emails saved as HTML files)\n";
        break;
    case SendMode::TEST_SEND:
        echo "TEST_SEND (emails sent to test addresses)\n";
        break;
    case SendMode::LIVE:
        echo "LIVE (emails sent to actual recipients)\n";
        break;
    default:
        echo "Unknown mode\n";
}
