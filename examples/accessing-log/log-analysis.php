<?php

require '../../vendor/autoload.php';

use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;

/**
 * Advanced SMTP log analysis and debugging utility
 */
class SMTPLogAnalyzer
{
    private $logs = [];
    
    public function __construct(array $logs)
    {
        $this->logs = $logs;
    }
    
    public function getConnectionPhase(): array
    {
        $connectionLogs = [];
        foreach ($this->logs as $log) {
            $code = $log['response-code'] ?? '';
            if (in_array($code, ['220', '221'])) {
                $connectionLogs[] = $log;
            }
        }
        return $connectionLogs;
    }
    
    public function getAuthenticationPhase(): array
    {
        $authLogs = [];
        foreach ($this->logs as $log) {
            $code = $log['response-code'] ?? '';
            if (in_array($code, ['334', '235', '535'])) {
                $authLogs[] = $log;
            }
        }
        return $authLogs;
    }
    
    public function getMailTransactionPhase(): array
    {
        $mailLogs = [];
        foreach ($this->logs as $log) {
            $code = $log['response-code'] ?? '';
            if (in_array($code, ['250', '354', '550', '551', '552', '553', '554'])) {
                $mailLogs[] = $log;
            }
        }
        return $mailLogs;
    }
    
    public function hasErrors(): bool
    {
        foreach ($this->logs as $log) {
            $code = $log['response-code'] ?? '';
            if (strlen($code) > 0 && in_array($code[0], ['4', '5'])) {
                return true;
            }
        }
        return false;
    }
    
    public function getErrors(): array
    {
        $errors = [];
        foreach ($this->logs as $log) {
            $code = $log['response-code'] ?? '';
            if (strlen($code) > 0 && in_array($code[0], ['4', '5'])) {
                $errors[] = $log;
            }
        }
        return $errors;
    }
    
    public function getSuccessRate(): float
    {
        if (empty($this->logs)) {
            return 0.0;
        }
        
        $successCount = 0;
        foreach ($this->logs as $log) {
            $code = $log['response-code'] ?? '';
            if (in_array($code, ['220', '221', '250', '235', '354'])) {
                $successCount++;
            }
        }
        
        return ($successCount / count($this->logs)) * 100;
    }
}

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

// Create multiple emails for comprehensive logging
$emails = [];

// Email 1: Simple email
$email1 = new Email($smtpAccount);
$email1->subject('Log Analysis Test #1')
       ->to('test1@example.com')
       ->insert('p')->text('First test email for log analysis.');

// Email 2: Email with attachments
$email2 = new Email($smtpAccount);
$email2->subject('Log Analysis Test #2')
       ->to('test2@example.com')
       ->insert('p')->text('Second test email with potential attachment.');

// Email 3: Email with multiple recipients
$email3 = new Email($smtpAccount);
$email3->subject('Log Analysis Test #3')
       ->to('test3@example.com')
       ->cc('cc@example.com')
       ->insert('p')->text('Third test email with multiple recipients.');

$emails = [$email1, $email2, $email3];

echo "SMTP LOG ANALYSIS UTILITY\n";
echo str_repeat("=", 60) . "\n\n";

$allLogs = [];
$emailResults = [];

// Send emails and collect logs
foreach ($emails as $index => $email) {
    $emailNum = $index + 1;
    echo "Processing Email #$emailNum: " . $email->getSubject() . "\n";
    echo str_repeat("-", 40) . "\n";
    
    try {
        $email->send();
        $status = "✅ SUCCESS";
    } catch (Exception $e) {
        $status = "❌ FAILED: " . $e->getMessage();
    }
    
    $logs = $email->getLog();
    $analyzer = new SMTPLogAnalyzer($logs);
    
    $emailResults[] = [
        'email' => $email,
        'status' => $status,
        'logs' => $logs,
        'analyzer' => $analyzer
    ];
    
    $allLogs = array_merge($allLogs, $logs);
    
    echo "Status: $status\n";
    echo "Log entries: " . count($logs) . "\n";
    echo "Success rate: " . number_format($analyzer->getSuccessRate(), 1) . "%\n";
    echo "Has errors: " . ($analyzer->hasErrors() ? "Yes" : "No") . "\n\n";
}

// Overall analysis
echo "OVERALL ANALYSIS\n";
echo str_repeat("=", 60) . "\n";
$overallAnalyzer = new SMTPLogAnalyzer($allLogs);

echo "Total log entries: " . count($allLogs) . "\n";
echo "Overall success rate: " . number_format($overallAnalyzer->getSuccessRate(), 1) . "%\n";
echo "Total errors found: " . count($overallAnalyzer->getErrors()) . "\n\n";

// Phase analysis
echo "SMTP PHASE ANALYSIS\n";
echo str_repeat("=", 60) . "\n";

$connectionLogs = $overallAnalyzer->getConnectionPhase();
$authLogs = $overallAnalyzer->getAuthenticationPhase();
$mailLogs = $overallAnalyzer->getMailTransactionPhase();

echo "Connection phase entries: " . count($connectionLogs) . "\n";
echo "Authentication phase entries: " . count($authLogs) . "\n";
echo "Mail transaction phase entries: " . count($mailLogs) . "\n\n";

// Error analysis
if ($overallAnalyzer->hasErrors()) {
    echo "ERROR ANALYSIS\n";
    echo str_repeat("=", 60) . "\n";
    
    $errors = $overallAnalyzer->getErrors();
    foreach ($errors as $index => $error) {
        echo "Error #" . ($index + 1) . ":\n";
        echo "  Code: " . ($error['response-code'] ?? 'N/A') . "\n";
        echo "  Message: " . ($error['response-message'] ?? 'N/A') . "\n";
        echo "  Command: " . ($error['command'] ?? 'N/A') . "\n\n";
    }
}

// Performance metrics
echo "PERFORMANCE METRICS\n";
echo str_repeat("=", 60) . "\n";

$avgLogsPerEmail = count($allLogs) / count($emails);
echo "Average log entries per email: " . number_format($avgLogsPerEmail, 1) . "\n";

// Most common response codes
$codeCounts = [];
foreach ($allLogs as $log) {
    $code = $log['response-code'] ?? 'Unknown';
    $codeCounts[$code] = ($codeCounts[$code] ?? 0) + 1;
}

arsort($codeCounts);
echo "\nMost common response codes:\n";
foreach (array_slice($codeCounts, 0, 5) as $code => $count) {
    echo "  $code: $count occurrences\n";
}

// Generate detailed report
$reportFile = __DIR__ . '/detailed-log-analysis.txt';
$report = "SMTP LOG ANALYSIS REPORT\n";
$report .= "Generated: " . date('Y-m-d H:i:s') . "\n";
$report .= str_repeat("=", 60) . "\n\n";

$report .= "SUMMARY\n";
$report .= "Total emails processed: " . count($emails) . "\n";
$report .= "Total log entries: " . count($allLogs) . "\n";
$report .= "Overall success rate: " . number_format($overallAnalyzer->getSuccessRate(), 1) . "%\n";
$report .= "Errors found: " . count($overallAnalyzer->getErrors()) . "\n\n";

foreach ($emailResults as $index => $result) {
    $emailNum = $index + 1;
    $report .= "EMAIL #$emailNum DETAILS\n";
    $report .= str_repeat("-", 30) . "\n";
    $report .= "Subject: " . $result['email']->getSubject() . "\n";
    $report .= "Status: " . $result['status'] . "\n";
    $report .= "Log entries: " . count($result['logs']) . "\n";
    $report .= "Success rate: " . number_format($result['analyzer']->getSuccessRate(), 1) . "%\n\n";
    
    foreach ($result['logs'] as $logIndex => $log) {
        $report .= "  Entry " . ($logIndex + 1) . ": ";
        $report .= ($log['response-code'] ?? 'N/A') . " - ";
        $report .= ($log['response-message'] ?? 'N/A') . "\n";
    }
    $report .= "\n";
}

file_put_contents($reportFile, $report);
echo "\n📄 Detailed report saved to: $reportFile\n";

// Recommendations based on analysis
echo "\nRECOMMENDations\n";
echo str_repeat("=", 60) . "\n";

if ($overallAnalyzer->getSuccessRate() < 80) {
    echo "⚠️  Low success rate detected. Check SMTP configuration.\n";
}

if ($overallAnalyzer->hasErrors()) {
    echo "⚠️  Errors found in logs. Review error details above.\n";
}

if (count($authLogs) === 0) {
    echo "ℹ️  No authentication logs found. Verify SMTP auth is working.\n";
}

if (count($connectionLogs) === 0) {
    echo "⚠️  No connection logs found. Check SMTP server connectivity.\n";
}

echo "✅ Log analysis complete. Review the detailed report for more information.\n";
