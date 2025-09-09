<?php

require '../../vendor/autoload.php';

use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;
use webfiori\file\File;

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

// Set email details using fluent interface
$email->to('recipient@example.com', 'Recipient Name')
      ->subject('Email with Attachments')
      ->priority(1);

// Add email content
$email->insert('h2')->text('File Attachments Demo');
$email->insert('p')->text('This email demonstrates different ways to attach files using WebFiori Mailer.');

// Method 1: Attach file using file path (string)
$documentPath = __DIR__ . '/sample-files/document.pdf';
if (file_exists($documentPath)) {
    $email->addAttachment($documentPath);
    $email->insert('p')->text('✅ Attached: document.pdf (using file path)');
} else {
    $email->insert('p')->text('⚠️ Sample document.pdf not found');
}

// Method 2: Attach file using File object
$imagePath = __DIR__ . '/sample-files/image.jpg';
if (file_exists($imagePath)) {
    $imageFile = new File($imagePath);
    $email->addAttachment($imageFile);
    $email->insert('p')->text('✅ Attached: image.jpg (using File object)');
} else {
    $email->insert('p')->text('⚠️ Sample image.jpg not found');
}

// Method 3: Using fluent interface
$textPath = __DIR__ . '/sample-files/readme.txt';
if (file_exists($textPath)) {
    $email->attach($textPath);
    $email->insert('p')->text('✅ Attached: readme.txt (using fluent interface)');
} else {
    $email->insert('p')->text('⚠️ Sample readme.txt not found');
}

// Display attachment information
$attachments = $email->getAttachments();
$email->insert('hr');
$email->insert('h3')->text('Attachment Summary');
$email->insert('p')->text('Total attachments: ' . count($attachments));

if (!empty($attachments)) {
    $list = $email->insert('ul');
    foreach ($attachments as $attachment) {
        $list->addChild('li')->text($attachment->getName() . ' (' . $attachment->getSize() . ' bytes)');
    }
}

// Send the email
try {
    $email->send();
    echo "Email with attachments sent successfully!\n";
    echo "Total attachments: " . count($attachments) . "\n";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}
