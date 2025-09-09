# Sending Modes Examples

This folder demonstrates the different sending modes available in WebFiori Mailer for development, testing, and production environments.

## Examples

### 🧪 test-mode.php
Shows how to use `TEST_STORE` mode:
- Store emails as HTML files instead of sending
- Preview email content and layout
- Safe testing without sending real emails

### 📧 test-send-mode.php
Demonstrates `TEST_SEND` mode:
- Redirect emails to test addresses
- Preserve original recipient information
- Test email delivery without bothering real users

### 🚀 production-mode.php
Production-ready example with environment-based configuration:
- Environment-aware mode selection
- Production vs development configuration
- Best practices for live email sending

## Sending Modes

### TEST_STORE Mode
Stores emails as HTML files for preview without sending.

```php
$email->setMode(SendMode::TEST_STORE, [
    'store-path' => '/path/to/storage/directory'
]);
```

**Use Cases:**
- Email template development
- Content preview and testing
- Layout verification
- Debugging email structure

### TEST_SEND Mode
Sends emails to specified test addresses instead of original recipients.

```php
$email->setMode(SendMode::TEST_SEND, [
    'send-addresses' => [
        'developer@company.com',
        'qa-team@company.com'
    ]
]);
```

**Use Cases:**
- Integration testing
- QA verification
- Staging environment testing
- Safe delivery testing

### LIVE Mode (Default)
Sends emails to actual recipients - production mode.

```php
$email->setMode(SendMode::LIVE);
// or simply don't set any mode (default)
```

**Use Cases:**
- Production environment
- Real email delivery
- Customer communications

## Environment-Based Configuration

### Development Environment
```php
$environment = getenv('APP_ENV') ?: 'development';

if ($environment === 'production') {
    $email->setMode(SendMode::LIVE);
} else {
    $email->setMode(SendMode::TEST_STORE, [
        'store-path' => __DIR__ . '/email-previews'
    ]);
}
```

### Configuration Examples

#### .env File
```bash
# Development
APP_ENV=development
EMAIL_MODE=test_store
EMAIL_PREVIEW_PATH=/var/www/email-previews

# Staging
APP_ENV=staging
EMAIL_MODE=test_send
EMAIL_TEST_ADDRESSES=qa@company.com,dev@company.com

# Production
APP_ENV=production
EMAIL_MODE=live
```

#### PHP Configuration
```php
$config = [
    'development' => [
        'mode' => SendMode::TEST_STORE,
        'options' => ['store-path' => '/tmp/email-previews']
    ],
    'staging' => [
        'mode' => SendMode::TEST_SEND,
        'options' => ['send-addresses' => ['qa@company.com']]
    ],
    'production' => [
        'mode' => SendMode::LIVE,
        'options' => []
    ]
];

$env = getenv('APP_ENV') ?: 'development';
$email->setMode($config[$env]['mode'], $config[$env]['options']);
```

## Running the Examples

```bash
# Navigate to sending-modes directory
cd examples/sending-modes

# Run test storage mode
php test-mode.php

# Run test send mode
php test-send-mode.php

# Run production mode (development)
php production-mode.php

# Run production mode (production)
APP_ENV=production php production-mode.php
```

## File Storage Structure

When using `TEST_STORE` mode, emails are saved with this structure:

```
email-previews/
├── Email Subject Name/
│   └── YYYY-MM-DD_HH-MM-SS.html
└── Another Email Subject/
    └── YYYY-MM-DD_HH-MM-SS.html
```

### Example Storage Path
```
email-previews/
└── Test Mode Demo - Email Storage/
    └── 2024-01-15_14-30-25.html
```

## Best Practices

### Development Workflow
1. **Local Development**: Use `TEST_STORE` mode
2. **Integration Testing**: Use `TEST_SEND` mode
3. **Staging**: Use `TEST_SEND` with staging addresses
4. **Production**: Use `LIVE` mode

### Security Considerations
- Never use production credentials in development
- Validate environment configuration
- Implement proper access controls for stored emails
- Use secure directories for email previews

### Error Handling
```php
try {
    $email->send();
    
    switch ($email->getMode()) {
        case SendMode::TEST_STORE:
            echo "Email stored for preview\n";
            break;
        case SendMode::TEST_SEND:
            echo "Email sent to test addresses\n";
            break;
        case SendMode::LIVE:
            echo "Email sent to recipients\n";
            break;
    }
} catch (Exception $e) {
    error_log("Email error: " . $e->getMessage());
    
    if ($email->getMode() === SendMode::LIVE) {
        // Handle production errors differently
        notifyAdministrators($e);
    }
}
```

### Monitoring and Logging
```php
// Log email activity based on mode
$logMessage = sprintf(
    "Email sent - Mode: %s, Subject: %s, Recipients: %d",
    $email->getMode(),
    $email->getSubject(),
    count($email->getTo())
);

if ($email->getMode() === SendMode::LIVE) {
    // Production logging
    error_log($logMessage, 3, '/var/log/email-production.log');
} else {
    // Development logging
    error_log($logMessage, 3, '/var/log/email-development.log');
}
```

## Troubleshooting

### Common Issues
- **Permission denied**: Check write permissions for storage directory
- **Mode not working**: Verify mode constants and configuration
- **Test addresses not receiving**: Check SMTP configuration and test email addresses

### Debug Information
```php
echo "Current mode: " . $email->getMode() . "\n";
echo "Recipients: " . count($email->getTo()) . "\n";
echo "Subject: " . $email->getSubject() . "\n";
```
