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

// Create and send email using fluent interface
try {
    $email = new Email($smtpAccount);
    
    $email->to('recipient@example.com', 'Recipient Name')
          ->cc('manager@example.com', 'Manager')
          ->subject('Fluent Interface Demo')
          ->priority(1);
    
    // Add content
    $email->insert('h2')->text('Fluent Interface Example');
    $email->insert('p')->text('This email was created using method chaining for a cleaner, more readable syntax.');
    
    // Send the email
    $email->send();
    echo "Email sent successfully using fluent interface!\n";
    
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}
