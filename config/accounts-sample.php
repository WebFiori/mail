<?php
/**
 * Sample SMTP accounts configuration for testing.
 * Copy this file to accounts.php and update with your credentials.
 */

use WebFiori\Mail\AccountOption;

return [
    // Traditional SMTP Authentication
    'other-smtp-1' => [
        AccountOption::SERVER_ADDRESS => 'mail.programmingacademia.com',
        AccountOption::PORT => 587,
        AccountOption::USERNAME => getenv('OTHER_USERNAME_1') ?: 'test@programmingacademia.com',
        AccountOption::PASSWORD => getenv('OTHER_PASSWORD_1') ?: 'your-password',
        AccountOption::SENDER_ADDRESS => 'programmingacademia.com',
        AccountOption::SENDER_NAME => 'Test Sender 1',
        AccountOption::NAME => 'other-test-1'
    ],
    'gmail' => [
        AccountOption::SERVER_ADDRESS => 'smtp.gmail.com',
        AccountOption::PORT => 587,
        AccountOption::USERNAME => getenv('GMAIL_USERNAME') ?: 'your-email@gmail.com',
        AccountOption::PASSWORD => getenv('GMAIL_PASSWORD') ?: 'your-app-password',
        AccountOption::SENDER_ADDRESS => getenv('GMAIL_USERNAME') ?: 'your-email@gmail.com',
        AccountOption::SENDER_NAME => 'Test Sender 3',
        AccountOption::NAME => 'gmail-test'
    ],

    // OAuth2 Configuration
    'oauth' => [
        'microsoft' => [
            AccountOption::SERVER_ADDRESS => 'smtp.office365.com',
            AccountOption::PORT => 587,
            AccountOption::USERNAME => getenv('OUTLOOK_USERNAME') ?: 'your-email@outlook.com',
            AccountOption::SENDER_ADDRESS => getenv('OUTLOOK_USERNAME') ?: 'your-email@outlook.com',
            AccountOption::SENDER_NAME => 'Test Sender 4',
            AccountOption::NAME => 'outlook-oauth-test',
            AccountOption::ACCESS_TOKEN => getenv('OUTLOOK_TOKEN') ?: 'your-access-token',
        ],
        
        'google' => [
            AccountOption::SERVER_ADDRESS => 'smtp.gmail.com',
            AccountOption::PORT => 587,
            AccountOption::USERNAME => getenv('GMAIL_USERNAME') ?: 'your-email@gmail.com',
            AccountOption::SENDER_ADDRESS => getenv('GMAIL_USERNAME') ?: 'your-email@gmail.com',
            AccountOption::SENDER_NAME => 'Test Sender 5',
            AccountOption::NAME => 'gmail-oauth-test',
            AccountOption::ACCESS_TOKEN => getenv('GMAIL_TOKEN') ?: 'your-access-token',
        ]
    ],

    // Test Recipients
    'recipients' => [
        'to' => getenv('TEST_RECIPIENT_TO') ?: 'test-recipient@example.com',
        'cc' => getenv('TEST_RECIPIENT_CC') ?: 'cc-recipient@example.com',
        'bcc' => getenv('TEST_RECIPIENT_BCC') ?: 'bcc-recipient@example.com'
    ]
];
