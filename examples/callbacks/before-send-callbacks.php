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

// Set basic email details
$email->subject('Before Send Callbacks Demo')
      ->to('recipient@example.com', 'Recipient');

// Add initial content
$email->insert('h1')->text('Before Send Callbacks');
$email->insert('p')->text('This email demonstrates various before-send callback functionalities.');

// Callback 1: Add dynamic content based on current time
$email->addBeforeSend(function (Email $email) {
    $currentHour = (int)date('H');
    
    if ($currentHour < 12) {
        $greeting = 'Good Morning!';
        $timeClass = 'morning';
    } elseif ($currentHour < 17) {
        $greeting = 'Good Afternoon!';
        $timeClass = 'afternoon';
    } else {
        $greeting = 'Good Evening!';
        $timeClass = 'evening';
    }
    
    $greetingDiv = $email->insert('div', [
        'class' => $timeClass,
        'style' => [
            'background' => '#e8f4fd',
            'padding' => '10px',
            'border-radius' => '5px',
            'margin' => '10px 0'
        ]
    ]);
    $greetingDiv->addChild('h3')->text($greeting);
    $greetingDiv->addChild('p')->text('This greeting was added dynamically based on the current time: ' . date('H:i:s'));
});

// Callback 2: Add user-specific personalization
$userData = [
    'name' => 'John Doe',
    'last_login' => '2024-01-10 15:30:00',
    'account_type' => 'Premium'
];

$email->addBeforeSend(function (Email $email, array $userData) {
    $personalDiv = $email->insert('div', [
        'style' => [
            'background' => '#f0f8e8',
            'padding' => '15px',
            'border-left' => '4px solid #28a745',
            'margin' => '15px 0'
        ]
    ]);
    
    $personalDiv->addChild('h3')->text('Personal Information');
    $personalDiv->addChild('p')->text('Hello ' . $userData['name'] . '!');
    $personalDiv->addChild('p')->text('Account Type: ' . $userData['account_type']);
    $personalDiv->addChild('p')->text('Last Login: ' . $userData['last_login']);
    
}, [$userData]);

// Callback 3: Add system information and metadata
$email->addBeforeSend(function (Email $email) {
    $systemInfo = $email->insert('div', [
        'style' => [
            'background' => '#fff3cd',
            'padding' => '10px',
            'border' => '1px solid #ffeaa7',
            'border-radius' => '3px',
            'margin' => '10px 0',
            'font-size' => '12px'
        ]
    ]);
    
    $systemInfo->addChild('h4')->text('System Information');
    $infoList = $systemInfo->addChild('ul');
    $infoList->addChild('li')->text('Server: ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
    $infoList->addChild('li')->text('PHP Version: ' . PHP_VERSION);
    $infoList->addChild('li')->text('Timestamp: ' . date('Y-m-d H:i:s T'));
    $infoList->addChild('li')->text('Email ID: ' . uniqid('email_'));
});

// Callback 4: Validate and modify email before sending
$email->addBeforeSend(function (Email $email) {
    // Add validation
    if (empty($email->getTo())) {
        throw new Exception('No recipients specified');
    }
    
    if (strlen($email->getSubject()) < 5) {
        throw new Exception('Subject too short');
    }
    
    // Add footer automatically
    $email->insert('hr');
    $footer = $email->insert('div', [
        'style' => [
            'font-size' => '11px',
            'color' => '#666',
            'text-align' => 'center',
            'margin-top' => '20px',
            'padding-top' => '10px',
            'border-top' => '1px solid #eee'
        ]
    ]);
    
    $footer->addChild('p')->text('This email was sent automatically by WebFiori Mailer.');
    $footer->addChild('p')->text('© 2024 Your Company Name. All rights reserved.');
    
    echo "✅ Email validation passed and footer added.\n";
});

// Callback 5: Logging and analytics
$email->addBeforeSend(function (Email $email) {
    $logEntry = [
        'timestamp' => date('c'),
        'subject' => $email->getSubject(),
        'recipients' => array_keys($email->getTo()),
        'cc_recipients' => array_keys($email->getCC()),
        'bcc_recipients' => array_keys($email->getBCC()),
        'priority' => $email->getPriority(),
        'attachments' => count($email->getAttachments())
    ];
    
    // Log to file
    $logFile = __DIR__ . '/email-send-log.json';
    $existingLogs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
    $existingLogs[] = $logEntry;
    file_put_contents($logFile, json_encode($existingLogs, JSON_PRETTY_PRINT));
    
    echo "📊 Email logged for analytics.\n";
});

// Display callback information
echo "BEFORE SEND CALLBACKS DEMO\n";
echo str_repeat("=", 50) . "\n";
echo "Email Subject: " . $email->getSubject() . "\n";
echo "Recipients: " . implode(', ', array_keys($email->getTo())) . "\n";
echo "Callbacks registered: 5\n\n";

echo "Executing callbacks and sending email...\n";
echo str_repeat("-", 30) . "\n";

// Send the email (callbacks will execute automatically)
try {
    $email->send();
    echo "✅ Email sent successfully with all callbacks executed!\n";
} catch (Exception $e) {
    echo "❌ Failed to send email: " . $e->getMessage() . "\n";
}

// Display what callbacks accomplished
echo "\nCALLBACK RESULTS\n";
echo str_repeat("=", 50) . "\n";
echo "1. ⏰ Time-based greeting added\n";
echo "2. 👤 User personalization applied\n";
echo "3. 🖥️  System information included\n";
echo "4. ✅ Email validation and footer added\n";
echo "5. 📊 Analytics data logged\n";

// Show log file location
$logFile = __DIR__ . '/email-send-log.json';
if (file_exists($logFile)) {
    echo "\n📄 Send log saved to: " . $logFile . "\n";
}

// Example of conditional callbacks
echo "\nCONDITIONAL CALLBACK EXAMPLE\n";
echo str_repeat("=", 50) . "\n";

$conditionalEmail = new Email($smtpAccount);
$conditionalEmail->subject('Conditional Callback Test')
                 ->to('test@example.com');

// Add content
$conditionalEmail->insert('p')->text('This demonstrates conditional callback execution.');

// Conditional callback based on environment
$environment = getenv('APP_ENV') ?: 'development';

if ($environment === 'production') {
    $conditionalEmail->addBeforeSend(function (Email $email) {
        // Production-specific callback
        $email->insert('div', ['style' => ['color' => 'green']])
              ->addChild('p')->text('✅ Production environment - all systems operational.');
    });
} else {
    $conditionalEmail->addBeforeSend(function (Email $email) {
        // Development-specific callback
        $email->insert('div', ['style' => ['color' => 'orange']])
              ->addChild('p')->text('⚠️ Development environment - for testing only.');
    });
}

echo "Environment: " . $environment . "\n";
echo "Conditional callback will be applied based on environment.\n";
