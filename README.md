# WebFiori Mailer

Sockets-based library for sending HTML email messages.

<p align="center">
  <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php84.yaml">
    <img src="https://github.com/WebFiori/mail/actions/workflows/php84.yaml/badge.svg?branch=main">
  </a>
  <a href="https://codecov.io/gh/WebFiori/mail">
    <img src="https://codecov.io/gh/WebFiori/mail/branch/main/graph/badge.svg" />
  </a>
  <a href="https://sonarcloud.io/dashboard?id=WebFiori_mail">
      <img src="https://sonarcloud.io/api/project_badges/measure?project=WebFiori_mail&metric=alert_status" />
  </a>
  <a href="https://github.com/WebFiori/mail/releases">
      <img src="https://img.shields.io/github/release/WebFiori/mail.svg?label=latest" />
  </a>
  <a href="https://packagist.org/packages/webfiori/mailer">
    <img src="https://img.shields.io/packagist/dt/webfiori/mailer?color=light-green">
  </a>
  <img src="https://img.shields.io/badge/php-%3E%3D8.1-blue" alt="PHP 8.1+">
</p>

## Table of Contents

- [Motivation](#motivation)
- [Supported PHP Versions](#supported-php-versions)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Usage](#usage)
  - [Basic Usage](#basic-usage)
  - [Fluent Interface](#fluent-interface)
  - [OAuth Authentication](#oauth-authentication)
  - [Attachments](#attachments)
  - [Before Send Callback](#before-send-callback)
  - [After Send Callback](#after-send-callback)
  - [Accessing SMTP Log](#accessing-smtp-log)
  - [Storing Email](#storing-email)
  - [Testing Modes](#testing-modes)
- [Examples](#examples)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)
- [Changelog](#changelog)

## Motivation

### HTML-First Composition

Most mailing libraries treat the email body as a string you pass in. WebFiori Mailer treats emails as **HTML documents you build** using a virtual DOM. You compose emails the same way you'd build a web page — inserting elements, nesting children, setting styles programmatically:

```php
$div = $email->insert('div');
$div->addChild('p')->text('Hello!');
$div->addChild('p', ['style' => ['color' => 'red']])->text('Important message.');
```

No raw HTML strings. No template engine required (though templates are supported too).

### Minimal Dependencies

The library relies only on two lightweight packages (`webfiori/ui` for DOM building and `webfiori/file` for attachments). The SMTP layer is built directly on PHP sockets. No heavy third-party dependencies to keep up with.

### Built-in Testing Modes

Testing emails shouldn't require actually sending them or setting up third-party services:

- **Store mode** — Renders the email as a local HTML file for visual inspection, complete with headers metadata.
- **Test send mode** — Redirects messages to specified test addresses regardless of the actual recipients.

Switch between modes with a single call:

```php
$email->setMode(SendMode::TEST_STORE, ['store-path' => '/tmp/emails']);
```

### SMTP Transparency

Every command sent to the SMTP server is logged with its response code and message. When something fails, you see exactly what happened at the protocol level — no black-box debugging.

### Lightweight and Self-Contained

The entire library is a handful of classes. If all you need is to send HTML emails over SMTP without pulling in a large framework or dozens of transitive dependencies, this gets the job done.

## Supported PHP Versions

|                                                                                        Build Status                                                                                        |
|:------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php81.yaml"><img src="https://github.com/WebFiori/mail/actions/workflows/php81.yaml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php82.yaml"><img src="https://github.com/WebFiori/mail/actions/workflows/php82.yaml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php83.yaml"><img src="https://github.com/WebFiori/mail/actions/workflows/php83.yaml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php84.yaml"><img src="https://github.com/WebFiori/mail/actions/workflows/php84.yaml/badge.svg?branch=main"></a>  |

## Installation

```bash
composer require webfiori/mailer
```

## Quick Start

```php
<?php
use WebFiori\Mail\AccountOption;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\Email;

$smtp = new SMTPAccount([
    AccountOption::PORT => 465,
    AccountOption::SERVER_ADDRESS => 'mail.example.com',
    AccountOption::USERNAME => 'test@example.com',
    AccountOption::PASSWORD => 'secret',
    AccountOption::SENDER_NAME => 'My App',
    AccountOption::SENDER_ADDRESS => 'test@example.com',
    AccountOption::NAME => 'no-reply'
]);

$email = new Email($smtp);

$email->setSubject('Hello World From PHP 😀');
$email->addTo('recipient@example.com');

$div = $email->insert('div');
$div->addChild('p')->text('Hello World Message');
$div->addChild('p', ['style' => ['font-weight' => 'bold', 'color' => 'red']])
    ->text('This is just a test message.');

$email->send();
```

## Usage

### Basic Usage

#### Connecting to SMTP Server

Connection information is represented using an instance of the class [`WebFiori\Mail\SMTPAccount`](https://github.com/WebFiori/mail/blob/main/WebFiori/Mail/SMTPAccount.php).

```php
<?php
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;

$smtp = new SMTPAccount([
    AccountOption::PORT => 465,
    AccountOption::SERVER_ADDRESS => 'mail.example.com',
    AccountOption::USERNAME => 'test@example.com',
    AccountOption::PASSWORD => 'KnvcbxFYCz77',
    AccountOption::SENDER_NAME => 'Ibrahim',
    AccountOption::SENDER_ADDRESS => 'test@example.com',
    AccountOption::NAME => 'no-reply'
]);
```

#### Creating Email Message

```php
use WebFiori\Mail\Email;

$email = new Email($smtp);
```

#### Setting Email Subject

```php
$email->setSubject('Hello World From PHP 😀');
```

#### Adding a Recipient

```php
$email->addTo('recipient@example.com');
```

#### Writing HTML Content

The email messages are HTML based, utilizing the library [`webfiori/ui`](https://github.com/WebFiori/ui) to build the virtual DOM.

```php
$div = $email->insert('div');
$div->addChild('p')->text('Hello World Message');
$div->addChild('p', [
    'style' => [
        'font-weight' => 'bold',
        'color' => 'red'
    ]
])->text('This is just a test message.');
```

#### Sending The Message

```php
$email->send();
```

### Fluent Interface

WebFiori Mailer supports method chaining for a more readable and concise syntax:

```php
$email = new Email($smtpAccount);

$email->to('recipient@example.com', 'Recipient Name')
      ->cc('manager@example.com', 'Manager')
      ->subject('Hello from WebFiori Mailer!')
      ->priority(1)
      ->send();
```

Available fluent methods:
- `to()` - Add TO recipient
- `cc()` - Add CC recipient
- `bcc()` - Add BCC recipient
- `subject()` - Set email subject
- `attach()` - Add attachment
- `priority()` - Set email priority

### OAuth Authentication

WebFiori Mailer supports OAuth2 authentication for enhanced security with modern email providers:

#### Gmail OAuth

```php
$gmailAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.gmail.com',
    AccountOption::PORT => 587,
    AccountOption::USERNAME => 'your-email@gmail.com',
    AccountOption::ACCESS_TOKEN => 'your-oauth-access-token',
    AccountOption::SENDER_ADDRESS => 'your-email@gmail.com',
    AccountOption::SENDER_NAME => 'Your Name',
    AccountOption::NAME => 'gmail-oauth'
]);
```

#### Microsoft OAuth

```php
$microsoftAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp-mail.outlook.com',
    AccountOption::PORT => 587,
    AccountOption::USERNAME => 'your-email@outlook.com',
    AccountOption::ACCESS_TOKEN => 'your-microsoft-oauth-token',
    AccountOption::SENDER_ADDRESS => 'your-email@outlook.com',
    AccountOption::SENDER_NAME => 'Your Name',
    AccountOption::NAME => 'microsoft-oauth'
]);
```

See the [OAuth examples](examples/oauth-usage/) for complete setup instructions.

### SSL/TLS Verification

By default, SSL/TLS peer verification is **enabled** for all connections. This means:
- The server certificate is verified against your system's CA bundle (`verify_peer = true`)
- The certificate hostname must match the server address (`verify_peer_name = true`)
- Self-signed certificates are rejected (`allow_self_signed = false`)

This is the correct and secure default for any publicly trusted SMTP server.

#### Internal servers with self-signed certificates

```php
$account = new SMTPAccount([
    AccountOption::SERVER_ADDRESS  => 'mail.internal.example.com',
    AccountOption::PORT            => 587,
    AccountOption::USERNAME        => 'no-reply@internal.example.com',
    AccountOption::PASSWORD        => 'secret',
    AccountOption::SENDER_ADDRESS  => 'no-reply@internal.example.com',
    AccountOption::SENDER_NAME     => 'Internal Mailer',
    AccountOption::ALLOW_SELF_SIGNED => true,  // allow self-signed cert; verify_peer stays on
]);
```

#### Disable verification entirely (not recommended for production)

```php
$account = new SMTPAccount([
    // ...
    AccountOption::VERIFY_SSL => false,  // disables verify_peer and verify_peer_name
]);
```

> **Warning:** Setting `VERIFY_SSL` to `false` exposes SMTP credentials to man-in-the-middle attacks.
> Only use it in isolated/controlled environments.

See the [SSL configuration example](examples/basic-usage/ssl-configuration.php) for a full demonstration.

### Connection Retry and Timeout

Failed connections are automatically retried with exponential backoff. The read timeout applies to every `fgets()` call after the connection is established, preventing indefinite hangs when a server becomes unresponsive mid-session.

| Option | Default | Description |
|--------|---------|-------------|
| `AccountOption::MAX_RETRIES` | `3` | Number of reconnect attempts after failure |
| `AccountOption::RETRY_DELAY` | `1` | Base delay in seconds between attempts (doubles each retry) |

Backoff schedule with defaults: attempt 1 immediate, attempt 2 waits 1s, attempt 3 waits 2s, attempt 4 waits 4s.

```php
$account = new SMTPAccount([
    // ...
    AccountOption::MAX_RETRIES => 5,  // retry up to 5 times
    AccountOption::RETRY_DELAY => 2,  // backoff: 2s, 4s, 8s, 16s, 32s
]);

// Disable retries entirely:
$account->setMaxRetries(0);
```

> **Note:** Retries only apply to TCP connection establishment. A `SMTPException` thrown during `read()` due to a timeout means the session state is unknown and cannot be safely retried in place — the caller should reconnect from scratch.

> See [ADR-0032](https://github.com/WebFiori/docs/blob/main/adr/0032-smtp-timeout-and-retry-backoff.md) for the full design rationale.

### Message-ID and Reply Threading

Every sent email automatically gets a unique `Message-ID` header (RFC 5322). After calling `send()`, you can read it back:

```php
$email->send();
echo $email->getMessageId(); // e.g. <a3f2...@smtp.gmail.com>
```

To send a reply that threads correctly in email clients, set the `In-Reply-To` header:

```php
$reply = new Email($account);
$reply->setInReplyTo($originalEmail->getMessageId());
$reply->setSubject('Re: Original Subject');
$reply->addTo('sender@example.com');
$reply->send();
```

### Attachments

Attachments can be added using `Email::addAttachment()`. The parameter can be a file path string or an object of type `webfiori\file\File`.

```php
use webfiori\file\File;

$email->addAttachment('Attach00.txt');
$email->addAttachment(new File('another.txt'));
```

### Before Send Callback

Execute logic before the message is sent using `Email::addBeforeSend()`:

```php
$email->addBeforeSend(function (Email $e) {
    $e->insert('p')->text('This text is added before sending');
});
```

### After Send Callback

Execute logic after the message is sent using `Email::addAfterSend()`:

```php
$email->addAfterSend(function (Email $e) {
    // Do any action like storing the log.
});
```

### Accessing SMTP Log

Every SMTP command is logged with its response code and message. Access log events via `Email::getLog()`:

```php
foreach ($email->getLog() as $logEvent) {
    echo ' Command: '.$logEvent['command'];
    echo ' Code: '.$logEvent['response-code'];
    echo ' Message: '.$logEvent['response-message'];
}
```

### Storing Email

Emails can be stored as HTML web pages for preview purposes:

```php
$email->storeEmail('/path/to/email/file');
```

This will:
- Render the final email
- Create a folder named after the email subject
- Create an HTML file named with the current date and time

![image](https://github.com/WebFiori/mail/assets/12120015/abe81167-8743-4fd1-ab7a-c16d2bbd1411)

### Testing Modes

The library provides two testing modes controlled by `Email::setMode()`:

#### Store as Web Pages

```php
$email->setMode(SendMode::TEST_STORE, [
    'store-path' => '/path/to/store/message'
]);

$email->send(); // Stores instead of sending
```

#### Send to Test Addresses

```php
$email->setMode(SendMode::TEST_SEND, [
    'send-addresses' => [
        'addr1@example.com',
        'addr2@example.com',
    ]
]);

$email->send(); // Sends to test addresses only
```

## Examples

Comprehensive examples are available in the [`examples/`](examples/) directory:

- **[Basic Usage](examples/basic-usage/)** - Fundamental email sending and fluent interface
- **[OAuth Authentication](examples/oauth-usage/)** - Gmail and Microsoft OAuth integration
- **[File Attachments](examples/attachments/)** - Adding files to emails
- **[Sending Modes](examples/sending-modes/)** - Test, development, and production configurations
- **[SMTP Logging](examples/accessing-log/)** - Debugging and monitoring
- **[Callbacks](examples/callbacks/)** - Before/after send custom logic
- **[Custom Transports](examples/transports/)** - Connection reuse and pluggable transports

## Testing

```bash
# Install dependencies
composer install

# Run tests
composer test
```

## Contributing

Contributions are welcome! Please open an issue or submit a pull request on [GitHub](https://github.com/WebFiori/mail).

## License

This library is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.

## Support

If you encounter any issues, please [open an issue](https://github.com/WebFiori/mail/issues) on GitHub.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.
