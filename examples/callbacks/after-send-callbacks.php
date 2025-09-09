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
$email->subject('After Send Callbacks Demo')
      ->to('recipient@example.com', 'Recipient');

// Add email content
$email->insert('h1')->text('After Send Callbacks');
$email->insert('p')->text('This email demonstrates after-send callback functionality.');
$email->insert('p')->text('Various actions will be performed after this email is sent.');

// Callback 1: Log successful delivery
$email->addAfterSend(function (Email $email) {
    $logEntry = [
        'timestamp' => date('c'),
        'status' => 'sent',
        'subject' => $email->getSubject(),
        'recipients' => array_keys($email->getTo()),
        'smtp_server' => $email->getSMTPAccount()->getServerAddress(),
        'sender' => $email->getSMTPAccount()->getSenderAddress()
    ];
    
    $logFile = __DIR__ . '/delivery-log.json';
    $existingLogs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
    $existingLogs[] = $logEntry;
    file_put_contents($logFile, json_encode($existingLogs, JSON_PRETTY_PRINT));
    
    echo "📝 Delivery logged successfully.\n";
});

// Callback 2: Update database or external system
$email->addAfterSend(function (Email $email, array $config) {
    // Simulate database update
    $updateData = [
        'email_id' => uniqid('email_'),
        'sent_at' => date('Y-m-d H:i:s'),
        'subject' => $email->getSubject(),
        'recipient_count' => count($email->getTo()),
        'status' => 'delivered'
    ];
    
    // In real application, this would be a database insert/update
    $dbFile = __DIR__ . '/email_database.json';
    $records = file_exists($dbFile) ? json_decode(file_get_contents($dbFile), true) : [];
    $records[] = $updateData;
    file_put_contents($dbFile, json_encode($records, JSON_PRETTY_PRINT));
    
    echo "💾 Database updated with delivery information.\n";
}, ['db_config' => 'example']);

// Callback 3: Send notification to administrators
$email->addAfterSend(function (Email $email) {
    $notificationData = [
        'type' => 'email_sent',
        'timestamp' => date('c'),
        'details' => [
            'subject' => $email->getSubject(),
            'recipients' => count($email->getTo()),
            'server' => $email->getSMTPAccount()->getServerAddress()
        ]
    ];
    
    // Simulate admin notification (could be webhook, API call, etc.)
    $notificationFile = __DIR__ . '/admin-notifications.json';
    $notifications = file_exists($notificationFile) ? json_decode(file_get_contents($notificationFile), true) : [];
    $notifications[] = $notificationData;
    file_put_contents($notificationFile, json_encode($notifications, JSON_PRETTY_PRINT));
    
    echo "🔔 Administrator notified of email delivery.\n";
});

// Callback 4: Analytics and metrics collection
$email->addAfterSend(function (Email $email) {
    $metrics = [
        'timestamp' => time(),
        'date' => date('Y-m-d'),
        'hour' => (int)date('H'),
        'subject_length' => strlen($email->getSubject()),
        'recipient_count' => count($email->getTo()),
        'cc_count' => count($email->getCC()),
        'bcc_count' => count($email->getBCC()),
        'attachment_count' => count($email->getAttachments()),
        'priority' => $email->getPriority(),
        'smtp_logs' => count($email->getLog())
    ];
    
    $metricsFile = __DIR__ . '/email-metrics.json';
    $allMetrics = file_exists($metricsFile) ? json_decode(file_get_contents($metricsFile), true) : [];
    $allMetrics[] = $metrics;
    file_put_contents($metricsFile, json_encode($allMetrics, JSON_PRETTY_PRINT));
    
    echo "📊 Metrics collected for analytics.\n";
});

// Callback 5: Cleanup and maintenance tasks
$email->addAfterSend(function (Email $email) {
    // Cleanup temporary files (if any)
    $tempDir = __DIR__ . '/temp';
    if (is_dir($tempDir)) {
        $files = glob($tempDir . '/*');
        $cleanedCount = 0;
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < (time() - 3600)) { // 1 hour old
                unlink($file);
                $cleanedCount++;
            }
        }
        if ($cleanedCount > 0) {
            echo "🧹 Cleaned up $cleanedCount temporary files.\n";
        }
    }
    
    // Log cleanup activity
    $cleanupLog = [
        'timestamp' => date('c'),
        'action' => 'post_send_cleanup',
        'files_cleaned' => $cleanedCount ?? 0
    ];
    
    $cleanupFile = __DIR__ . '/cleanup-log.json';
    $cleanupLogs = file_exists($cleanupFile) ? json_decode(file_get_contents($cleanupFile), true) : [];
    $cleanupLogs[] = $cleanupLog;
    file_put_contents($cleanupFile, json_encode($cleanupLogs, JSON_PRETTY_PRINT));
});

// Display information before sending
echo "AFTER SEND CALLBACKS DEMO\n";
echo str_repeat("=", 50) . "\n";
echo "Email Subject: " . $email->getSubject() . "\n";
echo "Recipients: " . implode(', ', array_keys($email->getTo())) . "\n";
echo "After-send callbacks registered: 5\n\n";

echo "Sending email...\n";
echo str_repeat("-", 30) . "\n";

// Send the email
try {
    $email->send();
    echo "✅ Email sent successfully!\n\n";
    
    echo "After-send callbacks executed:\n";
    echo str_repeat("-", 30) . "\n";
    // Callbacks execute automatically after successful send
    
} catch (Exception $e) {
    echo "❌ Failed to send email: " . $e->getMessage() . "\n";
    echo "After-send callbacks will NOT execute on failure.\n";
}

// Display generated files
echo "\nGENERATED FILES\n";
echo str_repeat("=", 50) . "\n";

$generatedFiles = [
    'delivery-log.json' => 'Email delivery log',
    'email_database.json' => 'Simulated database records',
    'admin-notifications.json' => 'Administrator notifications',
    'email-metrics.json' => 'Analytics and metrics data',
    'cleanup-log.json' => 'Cleanup activity log'
];

foreach ($generatedFiles as $filename => $description) {
    $filepath = __DIR__ . '/' . $filename;
    if (file_exists($filepath)) {
        echo "📄 $filename - $description\n";
    }
}

// Example of error handling with callbacks
echo "\nERROR HANDLING EXAMPLE\n";
echo str_repeat("=", 50) . "\n";

// Create an email that will likely fail (invalid SMTP config)
$failingAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'invalid-server.example.com',
    AccountOption::PORT => 587,
    AccountOption::USERNAME => 'invalid@example.com',
    AccountOption::PASSWORD => 'invalid-password',
    AccountOption::SENDER_ADDRESS => 'invalid@example.com',
    AccountOption::SENDER_NAME => 'Test',
    AccountOption::NAME => 'failing-account'
]);

$failingEmail = new Email($failingAccount);
$failingEmail->subject('This will fail')
             ->to('test@example.com')
             ->insert('p')->text('This email is designed to fail.');

// Add after-send callback (won't execute on failure)
$failingEmail->addAfterSend(function (Email $email) {
    echo "This callback should NOT execute because sending will fail.\n";
});

echo "Attempting to send email with invalid configuration...\n";

try {
    $failingEmail->send();
    echo "Unexpected success!\n";
} catch (Exception $e) {
    echo "Expected failure: " . $e->getMessage() . "\n";
    echo "After-send callbacks correctly did NOT execute.\n";
}

// Show callback execution summary
echo "\nCALLBACK EXECUTION SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Successful email: All 5 after-send callbacks executed\n";
echo "❌ Failed email: 0 after-send callbacks executed (correct behavior)\n";
echo "\nAfter-send callbacks only execute when email sending succeeds.\n";
