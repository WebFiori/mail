# Basic Usage Examples

This folder demonstrates the fundamental features of WebFiori Mailer for sending emails.

## Examples

### 📧 basic-email.php
A complete example showing how to:
- Configure SMTP account settings
- Create an email instance
- Set subject and recipients
- Add HTML content to the email body
- Send the email with error handling

### 🔗 fluent-interface.php
Demonstrates the fluent interface for method chaining:
- Chain multiple method calls for cleaner code
- Set recipients, subject, and priority in one flow
- More readable and concise syntax

## Configuration

Before running these examples, update the SMTP configuration:

```php
$smtpAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'your-smtp-server.com',
    AccountOption::PORT => 587, // or 465 for SSL
    AccountOption::USERNAME => 'your-email@domain.com',
    AccountOption::PASSWORD => 'your-password',
    AccountOption::SENDER_ADDRESS => 'your-email@domain.com',
    AccountOption::SENDER_NAME => 'Your Display Name',
    AccountOption::NAME => 'account-identifier'
]);
```

## Common SMTP Settings

### Gmail
- **Server**: smtp.gmail.com
- **Port**: 587 (TLS) or 465 (SSL)
- **Security**: Use App Password, not regular password

### Outlook/Hotmail
- **Server**: smtp-mail.outlook.com
- **Port**: 587
- **Security**: Enable 2FA and use App Password

### Custom SMTP
- Check with your email provider for specific settings
- Ensure your hosting allows outbound SMTP connections

## Running the Examples

```bash
# Navigate to the basic-usage directory
cd examples/basic-usage

# Run the basic example
php basic-email.php

# Run the fluent interface example
php fluent-interface.php
```

## Key Methods Used

- `new SMTPAccount()` - Configure SMTP connection
- `new Email()` - Create email instance
- `setSubject()` - Set email subject
- `addTo()` - Add recipient
- `insert()` - Add HTML content
- `send()` - Send the email
- `to()`, `cc()`, `subject()`, `priority()` - Fluent interface methods

## Error Handling

Always wrap email sending in try-catch blocks to handle potential SMTP errors gracefully.
