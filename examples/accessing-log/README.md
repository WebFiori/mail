# Accessing Log Examples

This folder demonstrates how to access, analyze, and utilize SMTP logs in WebFiori Mailer for debugging and monitoring email delivery.

## Examples

### 📊 smtp-logging.php
Basic SMTP logging example:
- Access SMTP communication logs
- Analyze response codes and messages
- Save logs to files for later review
- Understand common SMTP response codes

### 🔍 log-analysis.php
Advanced log analysis utility:
- Comprehensive log analysis across multiple emails
- SMTP phase breakdown (connection, auth, mail transaction)
- Error detection and reporting
- Performance metrics and recommendations

## SMTP Log Structure

Each log entry contains three key components:

```php
[
    'command' => 'SMTP command sent to server',
    'response-code' => 'Server response code (e.g., 250)',
    'response-message' => 'Server response message'
]
```

### Example Log Entry
```php
[
    'command' => 'MAIL FROM:<sender@example.com>',
    'response-code' => '250',
    'response-message' => '2.1.0 Ok'
]
```

## Accessing Logs

### Basic Log Access
```php
// Send email
$email->send();

// Get all log entries
$logs = $email->getLog();

// Process each log entry
foreach ($logs as $logEntry) {
    echo "Command: " . $logEntry['command'] . "\n";
    echo "Code: " . $logEntry['response-code'] . "\n";
    echo "Message: " . $logEntry['response-message'] . "\n";
}
```

### Log Analysis
```php
$successCodes = ['220', '250', '354', '221', '235'];
$errorCodes = ['4', '5']; // 4xx and 5xx codes

foreach ($logs as $log) {
    $code = $log['response-code'] ?? '';
    
    if (in_array($code, $successCodes)) {
        echo "✅ Success: " . $log['response-message'] . "\n";
    } elseif (strlen($code) > 0 && in_array($code[0], $errorCodes)) {
        echo "❌ Error: " . $log['response-message'] . "\n";
    }
}
```

## SMTP Response Codes

### Success Codes (2xx)
- **220** - Service ready
- **221** - Service closing transmission channel
- **250** - Requested mail action okay, completed
- **235** - Authentication successful
- **354** - Start mail input; end with <CRLF>.<CRLF>

### Temporary Failure (4xx)
- **421** - Service not available, closing transmission channel
- **450** - Requested mail action not taken: mailbox unavailable
- **451** - Requested action aborted: local error in processing
- **452** - Requested action not taken: insufficient system storage

### Permanent Failure (5xx)
- **500** - Syntax error, command unrecognized
- **501** - Syntax error in parameters or arguments
- **502** - Command not implemented
- **503** - Bad sequence of commands
- **530** - Authentication required
- **535** - Authentication credentials invalid
- **550** - Requested action not taken: mailbox unavailable
- **554** - Transaction failed

## SMTP Communication Phases

### 1. Connection Phase
```
Client connects to server
Server: 220 smtp.example.com ESMTP ready
```

### 2. EHLO/HELO Phase
```
Client: EHLO client.example.com
Server: 250-smtp.example.com Hello client.example.com
Server: 250-AUTH PLAIN LOGIN
Server: 250 STARTTLS
```

### 3. Authentication Phase
```
Client: AUTH LOGIN
Server: 334 VXNlcm5hbWU6
Client: [base64 encoded username]
Server: 334 UGFzc3dvcmQ6
Client: [base64 encoded password]
Server: 235 Authentication successful
```

### 4. Mail Transaction Phase
```
Client: MAIL FROM:<sender@example.com>
Server: 250 2.1.0 Ok
Client: RCPT TO:<recipient@example.com>
Server: 250 2.1.5 Ok
Client: DATA
Server: 354 End data with <CR><LF>.<CR><LF>
Client: [email content]
Client: .
Server: 250 2.0.0 Ok: queued
```

### 5. Termination Phase
```
Client: QUIT
Server: 221 2.0.0 Bye
```

## Running the Examples

```bash
# Navigate to accessing-log directory
cd examples/accessing-log

# Run basic SMTP logging
php smtp-logging.php

# Run advanced log analysis
php log-analysis.php
```

## Log File Output

Both examples generate log files for persistent analysis:

### smtp-logs.txt
```
SMTP Log - 2024-01-15 14:30:25
==================================================
Subject: SMTP Logging Demo
Recipients: recipient@example.com
Total Entries: 12

Entry #1:
  Command: EHLO localhost
  Code: 250
  Message: smtp.gmail.com at your service
------------------------------
```

### detailed-log-analysis.txt
```
SMTP LOG ANALYSIS REPORT
Generated: 2024-01-15 14:30:25
============================================================

SUMMARY
Total emails processed: 3
Total log entries: 36
Overall success rate: 85.5%
Errors found: 2
```

## Debugging Common Issues

### Authentication Failures
```php
foreach ($logs as $log) {
    if ($log['response-code'] === '535') {
        echo "Authentication failed: " . $log['response-message'] . "\n";
        echo "Check username/password or use OAuth\n";
    }
}
```

### Connection Issues
```php
$hasConnection = false;
foreach ($logs as $log) {
    if ($log['response-code'] === '220') {
        $hasConnection = true;
        break;
    }
}

if (!$hasConnection) {
    echo "No connection established. Check server address and port.\n";
}
```

### Delivery Issues
```php
foreach ($logs as $log) {
    if (in_array($log['response-code'], ['550', '551', '552', '553'])) {
        echo "Delivery issue: " . $log['response-message'] . "\n";
    }
}
```

## Best Practices

### Log Management
- **Rotate logs** regularly to prevent disk space issues
- **Filter sensitive data** (passwords, tokens) from logs
- **Compress old logs** for long-term storage
- **Monitor log patterns** for recurring issues

### Security Considerations
- Never log authentication credentials
- Sanitize email content in logs
- Secure log file access permissions
- Consider log encryption for sensitive environments

### Performance Monitoring
```php
// Track email sending performance
$startTime = microtime(true);
$email->send();
$endTime = microtime(true);

$logs = $email->getLog();
$logCount = count($logs);
$duration = $endTime - $startTime;

echo "Sending took: " . number_format($duration, 3) . " seconds\n";
echo "SMTP exchanges: " . $logCount . "\n";
echo "Average per exchange: " . number_format($duration / $logCount, 3) . " seconds\n";
```

## Integration with Monitoring Systems

### Structured Logging
```php
$logData = [
    'timestamp' => date('c'),
    'email_subject' => $email->getSubject(),
    'recipient_count' => count($email->getTo()),
    'smtp_logs' => $email->getLog(),
    'success' => !$analyzer->hasErrors(),
    'error_count' => count($analyzer->getErrors())
];

// Send to monitoring system
file_put_contents('structured-logs.json', json_encode($logData) . "\n", FILE_APPEND);
```
