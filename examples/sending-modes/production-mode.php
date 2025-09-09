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

// Environment-based mode selection
$environment = getenv('APP_ENV') ?: 'development';
$isProduction = ($environment === 'production');

// Set email details
$email->subject('Production Mode Example')
      ->to('customer@example.com', 'Valued Customer')
      ->priority(1);

// Add content with environment information
$email->insert('h1')->text('Production Mode Email');
$email->insert('p')->text('This email demonstrates production mode usage with environment-based configuration.');

$envInfo = $email->insert('div', ['style' => ['background' => '#e8f5e8', 'padding' => '15px', 'border-radius' => '5px']]);
$envInfo->addChild('h3')->text('Environment Information:');
$envInfo->addChild('p')->text('Current Environment: ' . strtoupper($environment));
$envInfo->addChild('p')->text('Production Mode: ' . ($isProduction ? 'ENABLED' : 'DISABLED'));

// Configure sending mode based on environment
if ($isProduction) {
    // Production: Send to actual recipients
    $email->setMode(SendMode::LIVE);
    
    $prodInfo = $email->insert('div', ['style' => ['background' => '#d4edda', 'padding' => '10px', 'border-left' => '4px solid #28a745']]);
    $prodInfo->addChild('h4')->text('✅ Production Mode Active');
    $prodInfo->addChild('p')->text('Emails will be sent to actual recipients.');
    
} else {
    // Development/Testing: Use test mode
    $email->setMode(SendMode::TEST_STORE, [
        'store-path' => __DIR__ . '/email-previews'
    ]);
    
    $testInfo = $email->insert('div', ['style' => ['background' => '#fff3cd', 'padding' => '10px', 'border-left' => '4px solid #ffc107']]);
    $testInfo->addChild('h4')->text('⚠️ Test Mode Active');
    $testInfo->addChild('p')->text('Emails will be stored as HTML files for preview.');
}

// Add best practices information
$bestPractices = $email->insert('div');
$bestPractices->addChild('h3')->text('Production Best Practices:');
$practices = $bestPractices->addChild('ul');
$practices->addChild('li')->text('Use environment variables for configuration');
$practices->addChild('li')->text('Implement proper error handling and logging');
$practices->addChild('li')->text('Test thoroughly in staging environment');
$practices->addChild('li')->text('Monitor email delivery rates');
$practices->addChild('li')->text('Implement rate limiting for bulk emails');

// Create preview directory if in test mode
if (!$isProduction) {
    $previewDir = __DIR__ . '/email-previews';
    if (!is_dir($previewDir)) {
        mkdir($previewDir, 0755, true);
    }
}

// Send the email
try {
    $email->send();
    
    if ($isProduction) {
        echo "✅ Email sent successfully in PRODUCTION mode!\n";
        echo "Recipient: customer@example.com\n";
    } else {
        echo "📧 Email stored successfully in TEST mode!\n";
        echo "Check email-previews directory for HTML preview.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Failed to send email: " . $e->getMessage() . "\n";
    
    // In production, you might want to log this error
    if ($isProduction) {
        error_log("Email sending failed: " . $e->getMessage());
    }
}

// Display configuration summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "CONFIGURATION SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "Environment: " . $environment . "\n";
echo "Mode: " . ($isProduction ? "LIVE (Production)" : "TEST (Development)") . "\n";
echo "SMTP Server: " . $smtpAccount->getServerAddress() . "\n";
echo "Sender: " . $smtpAccount->getSenderAddress() . "\n";

// Show how to set environment for production
if (!$isProduction) {
    echo "\n" . str_repeat("-", 30) . "\n";
    echo "To enable production mode, set:\n";
    echo "export APP_ENV=production\n";
    echo str_repeat("-", 30) . "\n";
}
