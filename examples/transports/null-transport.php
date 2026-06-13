<?php
/**
 * Example: Custom transport for testing.
 *
 * This demonstrates creating a null transport that captures sent emails
 * without actually delivering them — useful for unit/integration testing.
 */
require '../../vendor/autoload.php';

use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\TransportInterface;

/**
 * A transport that stores emails in memory instead of sending them.
 */
class NullTransport implements TransportInterface {
    public array $sent = [];

    public function send(Email $message): void {
        $this->sent[] = $message;
    }

    public function getName(): string {
        return 'null';
    }
}

// Usage in tests
$account = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.example.com',
    AccountOption::PORT => 465,
    AccountOption::USERNAME => 'test@example.com',
    AccountOption::PASSWORD => 'password',
    AccountOption::SENDER_ADDRESS => 'test@example.com',
    AccountOption::SENDER_NAME => 'Test',
    AccountOption::NAME => 'test'
]);

$transport = new NullTransport();

$email = new Email($account);
$email->setSubject('Welcome!');
$email->addTo('user@example.com');
$email->insert('p')->text('Thank you for signing up.');
$email->send($transport);

// Verify the email was "sent"
echo "Emails captured: " . count($transport->sent) . "\n";
echo "Subject: " . $transport->sent[0]->getSubject() . "\n";
echo "Recipient: " . implode(', ', array_keys($transport->sent[0]->getTo())) . "\n";
