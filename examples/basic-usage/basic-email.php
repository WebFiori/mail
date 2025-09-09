<?php

require '../../vendor/autoload.php';

use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;

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
$email->setSubject('Hello from WebFiori Mailer!');
$email->addTo('recipient@example.com', 'Recipient Name');

// Add email content
$email->insert('h1')->text('Welcome!');
$email->insert('p')->text('This is a basic email sent using WebFiori Mailer.');
$email->insert('p')->text('The library makes it easy to send HTML emails with PHP.');

// Send the email
try {
    $email->send();
    echo "Email sent successfully!\n";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}
