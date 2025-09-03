<?php
/**
 * Sample SMTP accounts configuration for testing.
 * Copy this file to accounts.php and update with your credentials.
 */

use WebFiori\Mail\AccountOption;

return [
    // Traditional SMTP Authentication
    'outlook' => [
        AccountOption::SERVER_ADDRESS => getenv('OUTLOOK_SMTP_SERVER') ?: 'smtp.office365.com',
        AccountOption::PORT => getenv('OUTLOOK_SMTP_PORT') ?: 587,
        AccountOption::USERNAME => getenv('OUTLOOK_USERNAME') ?: 'your-email@outlook.com',
        AccountOption::PASSWORD => getenv('OUTLOOK_PASSWORD') ?: 'your-password',
        AccountOption::SENDER_ADDRESS => getenv('OUTLOOK_SENDER_ADDRESS') ?: 'your-email@outlook.com',
        AccountOption::SENDER_NAME => getenv('OUTLOOK_SENDER_NAME') ?: 'Test Sender',
        AccountOption::NAME => 'outlook-test'
    ],

    'gmail' => [
        AccountOption::SERVER_ADDRESS => getenv('GMAIL_SMTP_SERVER') ?: 'smtp.gmail.com',
        AccountOption::PORT => getenv('GMAIL_SMTP_PORT') ?: 587,
        AccountOption::USERNAME => getenv('GMAIL_USERNAME') ?: 'your-email@gmail.com',
        AccountOption::PASSWORD => getenv('GMAIL_PASSWORD') ?: 'your-app-password',
        AccountOption::SENDER_ADDRESS => getenv('GMAIL_SENDER_ADDRESS') ?: 'your-email@gmail.com',
        AccountOption::SENDER_NAME => getenv('GMAIL_SENDER_NAME') ?: 'Test Sender',
        AccountOption::NAME => 'gmail-test'
    ],

    // OAuth2 Configuration
    'oauth' => [
        'microsoft' => [
            AccountOption::CLIENT_ID => getenv('MICROSOFT_CLIENT_ID') ?: 'your-azure-client-id',
            AccountOption::CLIENT_SECRET => getenv('MICROSOFT_CLIENT_SECRET') ?: 'your-azure-client-secret',
            AccountOption::REDIRECT_URI => getenv('MICROSOFT_REDIRECT_URI') ?: 'http://localhost:8000/callback',
            AccountOption::TENANT => getenv('MICROSOFT_TENANT') ?: 'common',
            AccountOption::USERNAME => getenv('MICROSOFT_USERNAME') ?: 'your-email@outlook.com'
        ],
        
        'google' => [
            AccountOption::CLIENT_ID => getenv('GOOGLE_CLIENT_ID') ?: 'your-google-client-id',
            AccountOption::CLIENT_SECRET => getenv('GOOGLE_CLIENT_SECRET') ?: 'your-google-client-secret',
            AccountOption::REDIRECT_URI => getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost:8000/callback',
            AccountOption::USERNAME => getenv('GOOGLE_USERNAME') ?: 'your-email@gmail.com'
        ]
    ],

    // Test Recipients
    'recipients' => [
        'primary' => getenv('TEST_RECIPIENT_PRIMARY') ?: 'test-recipient@example.com',
        'secondary' => getenv('TEST_RECIPIENT_SECONDARY') ?: 'another-recipient@example.com'
    ]
];
