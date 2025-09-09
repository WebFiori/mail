# Callbacks Examples

This folder demonstrates how to use before-send and after-send callbacks in WebFiori Mailer to implement custom logic around email sending.

## Examples

### 🔄 before-send-callbacks.php
Comprehensive before-send callback examples:
- Dynamic content generation based on time
- User personalization with data injection
- System information and metadata addition
- Email validation and automatic footer insertion
- Logging and analytics preparation

### ✅ after-send-callbacks.php
Complete after-send callback demonstrations:
- Delivery logging and tracking
- Database updates and external system integration
- Administrator notifications
- Analytics and metrics collection
- Cleanup and maintenance tasks

## Callback Types

### Before-Send Callbacks
Execute **before** the email is sent to the SMTP server.

```php
$email->addBeforeSend(function (Email $email) {
    // Modify email content
    // Add dynamic data
    // Validate email
    // Log preparation
});
```

**Use Cases:**
- Dynamic content generation
- Email validation
- Content personalization
- Adding system information
- Pre-send logging

### After-Send Callbacks
Execute **after** the email is successfully sent.

```php
$email->addAfterSend(function (Email $email) {
    // Log delivery
    // Update database
    // Send notifications
    // Collect metrics
});
```

**Use Cases:**
- Delivery confirmation logging
- Database updates
- Analytics collection
- Cleanup tasks
- External system notifications

## Callback Parameters

### Basic Callback
```php
$email->addBeforeSend(function (Email $email) {
    // Access email object
    echo "Subject: " . $email->getSubject();
});
```

### Callback with Parameters
```php
$userData = ['name' => 'John', 'id' => 123];

$email->addBeforeSend(function (Email $email, array $userData) {
    $email->insert('p')->text('Hello ' . $userData['name']);
}, [$userData]);
```

### Multiple Parameters
```php
$config = ['api_key' => 'abc123'];
$user = ['name' => 'Jane', 'email' => 'jane@example.com'];

$email->addAfterSend(function (Email $email, array $config, array $user) {
    // Use both config and user data
}, [$config, $user]);
```

## Common Callback Patterns

### Dynamic Content Generation
```php
$email->addBeforeSend(function (Email $email) {
    $currentTime = date('H:i:s');
    $email->insert('p')->text('Generated at: ' . $currentTime);
});
```

### User Personalization
```php
$email->addBeforeSend(function (Email $email, array $user) {
    $greeting = "Hello " . $user['name'] . "!";
    $email->insert('h2')->text($greeting);
}, [$userData]);
```

### Email Validation
```php
$email->addBeforeSend(function (Email $email) {
    if (empty($email->getTo())) {
        throw new Exception('No recipients specified');
    }
    
    if (strlen($email->getSubject()) < 5) {
        throw new Exception('Subject too short');
    }
});
```

### Delivery Logging
```php
$email->addAfterSend(function (Email $email) {
    $logEntry = [
        'timestamp' => date('c'),
        'subject' => $email->getSubject(),
        'recipients' => array_keys($email->getTo()),
        'status' => 'sent'
    ];
    
    file_put_contents('delivery.log', json_encode($logEntry) . "\n", FILE_APPEND);
});
```

### Database Updates
```php
$email->addAfterSend(function (Email $email, PDO $db) {
    $stmt = $db->prepare("INSERT INTO email_log (subject, sent_at) VALUES (?, ?)");
    $stmt->execute([$email->getSubject(), date('Y-m-d H:i:s')]);
}, [$databaseConnection]);
```

## Running the Examples

```bash
# Navigate to callbacks directory
cd examples/callbacks

# Run before-send callbacks example
php before-send-callbacks.php

# Run after-send callbacks example
php after-send-callbacks.php
```

## Generated Files

Both examples create various files to demonstrate callback functionality:

### Before-Send Example Files
- `email-send-log.json` - Pre-send analytics data

### After-Send Example Files
- `delivery-log.json` - Email delivery tracking
- `email_database.json` - Simulated database records
- `admin-notifications.json` - Administrator alerts
- `email-metrics.json` - Analytics and metrics
- `cleanup-log.json` - Maintenance activity log

## Advanced Callback Techniques

### Conditional Callbacks
```php
$environment = getenv('APP_ENV') ?: 'development';

if ($environment === 'production') {
    $email->addAfterSend(function (Email $email) {
        // Production-specific logging
        error_log("Production email sent: " . $email->getSubject());
    });
} else {
    $email->addAfterSend(function (Email $email) {
        // Development-specific actions
        echo "Dev email sent: " . $email->getSubject() . "\n";
    });
}
```

### Callback Chaining
```php
// Multiple callbacks execute in order
$email->addBeforeSend($validationCallback);
$email->addBeforeSend($personalizationCallback);
$email->addBeforeSend($loggingCallback);

$email->addAfterSend($deliveryLogCallback);
$email->addAfterSend($metricsCallback);
$email->addAfterSend($cleanupCallback);
```

### Error Handling in Callbacks
```php
$email->addBeforeSend(function (Email $email) {
    try {
        // Risky operation
        $data = fetchExternalData();
        $email->insert('p')->text($data);
    } catch (Exception $e) {
        // Fallback content
        $email->insert('p')->text('Default content due to error');
        error_log("Callback error: " . $e->getMessage());
    }
});
```

### Callback with External Services
```php
$email->addAfterSend(function (Email $email, array $config) {
    // Send webhook notification
    $payload = [
        'event' => 'email_sent',
        'subject' => $email->getSubject(),
        'timestamp' => time()
    ];
    
    $ch = curl_init($config['webhook_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}, [$webhookConfig]);
```

## Best Practices

### Performance Considerations
- Keep callbacks lightweight and fast
- Avoid blocking operations in callbacks
- Use asynchronous processing for heavy tasks
- Consider callback execution time impact

### Error Handling
- Always wrap risky operations in try-catch blocks
- Provide fallback behavior for failed callbacks
- Log callback errors appropriately
- Don't let callback failures prevent email sending

### Security
- Validate callback parameters
- Sanitize data used in callbacks
- Avoid exposing sensitive information in logs
- Use secure methods for external API calls

### Debugging
```php
$email->addBeforeSend(function (Email $email) {
    if (defined('DEBUG') && DEBUG) {
        echo "Before send - Subject: " . $email->getSubject() . "\n";
        echo "Recipients: " . count($email->getTo()) . "\n";
    }
});

$email->addAfterSend(function (Email $email) {
    if (defined('DEBUG') && DEBUG) {
        echo "After send - SMTP logs: " . count($email->getLog()) . "\n";
    }
});
```

## Integration Examples

### With Logging Frameworks
```php
use Monolog\Logger;

$email->addAfterSend(function (Email $email, Logger $logger) {
    $logger->info('Email sent', [
        'subject' => $email->getSubject(),
        'recipients' => count($email->getTo())
    ]);
}, [$monologLogger]);
```

### With Queue Systems
```php
$email->addAfterSend(function (Email $email, $queueManager) {
    // Queue follow-up tasks
    $queueManager->push('send-follow-up', [
        'original_subject' => $email->getSubject(),
        'recipients' => array_keys($email->getTo())
    ]);
}, [$queueManager]);
```

### With Analytics Platforms
```php
$email->addAfterSend(function (Email $email, $analytics) {
    $analytics->track('email_sent', [
        'subject_length' => strlen($email->getSubject()),
        'recipient_count' => count($email->getTo()),
        'has_attachments' => count($email->getAttachments()) > 0
    ]);
}, [$analyticsClient]);
```
