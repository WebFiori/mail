<?php
/**
 * Example: Using SmtpTransport for connection reuse.
 *
 * This demonstrates sending multiple emails over a single SMTP connection,
 * which is more efficient than reconnecting for each message.
 */
require '../../vendor/autoload.php';

use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\SmtpTransport;

$account = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.gmail.com',
    AccountOption::PORT => 587,
    AccountOption::USERNAME => 'your-email@gmail.com',
    AccountOption::PASSWORD => 'your-app-password',
    AccountOption::SENDER_ADDRESS => 'your-email@gmail.com',
    AccountOption::SENDER_NAME => 'Your Name',
    AccountOption::NAME => 'gmail-account'
]);

// Create transport once — reuse for multiple emails
$transport = new SmtpTransport($account);

$recipients = [
    'alice@example.com',
    'bob@example.com',
    'carol@example.com',
];

foreach ($recipients as $recipient) {
    $email = new Email($account);
    $email->setSubject('Monthly Newsletter');
    $email->addTo($recipient);
    $email->insert('p')->text("Hello $recipient, here is your newsletter.");

    try {
        $email->send($transport);
        echo "Sent to $recipient\n";

        // Reset the connection for the next message
        $transport->getServer()->reset();
    } catch (Exception $e) {
        echo "Failed for $recipient: " . $e->getMessage() . "\n";
    }
}
