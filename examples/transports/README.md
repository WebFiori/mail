# Custom Transports

WebFiori Mailer supports pluggable transports via `TransportInterface`. This allows you to swap the delivery mechanism without changing your email composition code.

## SmtpTransport (Default)

The built-in `SmtpTransport` handles SMTP delivery. You can use it explicitly to reuse a single connection across multiple emails:

```php
$transport = new SmtpTransport($account);

foreach ($recipients as $recipient) {
    $email = new Email($account);
    $email->setSubject('Newsletter');
    $email->addTo($recipient);
    $email->insert('p')->text('Hello!');
    $email->send($transport);

    // Reset connection for next message
    $transport->getServer()->reset();
}
```

See [smtp-transport.php](smtp-transport.php) for the full example.

## Custom Transports

Implement `TransportInterface` to create your own transport:

```php
class NullTransport implements TransportInterface {
    public array $sent = [];

    public function send(Email $message): void {
        $this->sent[] = $message;
    }

    public function getName(): string {
        return 'null';
    }
}

$email->send(new NullTransport());
```

This is useful for:
- **Unit testing** — Capture emails without sending
- **API providers** — Implement SES, SendGrid, etc.
- **Logging** — Record all outgoing emails

See [null-transport.php](null-transport.php) for the full example.
