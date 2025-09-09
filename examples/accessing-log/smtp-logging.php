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
$email->subject('SMTP Logging Demo')
      ->to('recipient@example.com', 'Recipient')
      ->priority(1);

// Add content about logging
$email->insert('h1')->text('SMTP Logging Demonstration');
$email->insert('p')->text('This email demonstrates how to access and utilize SMTP logs in WebFiori Mailer.');

$logInfo = $email->insert('div', ['style' => ['background' => '#f8f9fa', 'padding' => '15px', 'border-radius' => '5px']]);
$logInfo->addChild('h3')->text('What SMTP Logs Contain:');
$logList = $logInfo->addChild('ul');
$logList->addChild('li')->text('SMTP commands sent to server');
$logList->addChild('li')->text('Server response codes');
$logList->addChild('li')->text('Server response messages');
$logList->addChild('li')->text('Connection and authentication details');

// Attempt to send email and capture logs
echo "Attempting to send email...\n";
echo str_repeat("=", 50) . "\n";

try {
    $email->send();
    echo "✅ Email sent successfully!\n\n";
} catch (Exception $e) {
    echo "❌ Email sending failed: " . $e->getMessage() . "\n\n";
}

// Access and display SMTP logs
$logs = $email->getLog();

echo "SMTP LOG ANALYSIS\n";
echo str_repeat("=", 50) . "\n";
echo "Total log entries: " . count($logs) . "\n\n";

if (!empty($logs)) {
    foreach ($logs as $index => $logEntry) {
        echo "Entry #" . ($index + 1) . ":\n";
        echo "  Command: " . ($logEntry['command'] ?? 'N/A') . "\n";
        echo "  Response Code: " . ($logEntry['response-code'] ?? 'N/A') . "\n";
        echo "  Response Message: " . ($logEntry['response-message'] ?? 'N/A') . "\n";
        echo str_repeat("-", 30) . "\n";
    }
} else {
    echo "No SMTP logs available.\n";
    echo "This might happen if:\n";
    echo "- Email was sent in test mode\n";
    echo "- Connection failed before SMTP communication\n";
    echo "- Logs were cleared or not captured\n";
}

// Analyze logs for common patterns
echo "\nLOG ANALYSIS\n";
echo str_repeat("=", 50) . "\n";

$successCodes = ['220', '250', '354', '221'];
$authCodes = ['334', '235'];
$errorCodes = ['4', '5']; // 4xx and 5xx codes

$successCount = 0;
$authCount = 0;
$errorCount = 0;

foreach ($logs as $logEntry) {
    $code = $logEntry['response-code'] ?? '';
    
    if (in_array($code, $successCodes)) {
        $successCount++;
    } elseif (in_array($code, $authCodes)) {
        $authCount++;
    } elseif (strlen($code) > 0 && in_array($code[0], $errorCodes)) {
        $errorCount++;
    }
}

echo "Success responses: " . $successCount . "\n";
echo "Authentication responses: " . $authCount . "\n";
echo "Error responses: " . $errorCount . "\n";

// Save logs to file for later analysis
$logFile = __DIR__ . '/smtp-logs.txt';
$logContent = "SMTP Log - " . date('Y-m-d H:i:s') . "\n";
$logContent .= str_repeat("=", 50) . "\n";
$logContent .= "Subject: " . $email->getSubject() . "\n";
$logContent .= "Recipients: " . implode(', ', array_keys($email->getTo())) . "\n";
$logContent .= "Total Entries: " . count($logs) . "\n\n";

foreach ($logs as $index => $logEntry) {
    $logContent .= "Entry #" . ($index + 1) . ":\n";
    $logContent .= "  Command: " . ($logEntry['command'] ?? 'N/A') . "\n";
    $logContent .= "  Code: " . ($logEntry['response-code'] ?? 'N/A') . "\n";
    $logContent .= "  Message: " . ($logEntry['response-message'] ?? 'N/A') . "\n";
    $logContent .= str_repeat("-", 30) . "\n";
}

file_put_contents($logFile, $logContent);
echo "\n📄 Logs saved to: " . $logFile . "\n";

// Display common SMTP response codes for reference
echo "\nCOMMON SMTP RESPONSE CODES\n";
echo str_repeat("=", 50) . "\n";
$responseCodes = [
    '220' => 'Service ready',
    '221' => 'Service closing transmission channel',
    '250' => 'Requested mail action okay, completed',
    '334' => 'Authentication challenge',
    '235' => 'Authentication successful',
    '354' => 'Start mail input',
    '421' => 'Service not available',
    '450' => 'Requested mail action not taken: mailbox unavailable',
    '451' => 'Requested action aborted: local error in processing',
    '452' => 'Requested action not taken: insufficient system storage',
    '500' => 'Syntax error, command unrecognized',
    '501' => 'Syntax error in parameters or arguments',
    '502' => 'Command not implemented',
    '503' => 'Bad sequence of commands',
    '504' => 'Command parameter not implemented',
    '521' => 'Server does not accept mail',
    '530' => 'Authentication required',
    '535' => 'Authentication credentials invalid',
    '550' => 'Requested action not taken: mailbox unavailable',
    '551' => 'User not local',
    '552' => 'Requested mail action aborted: exceeded storage allocation',
    '553' => 'Requested action not taken: mailbox name not allowed',
    '554' => 'Transaction failed'
];

foreach ($responseCodes as $code => $description) {
    echo $code . " - " . $description . "\n";
}
