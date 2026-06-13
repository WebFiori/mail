<?php
namespace WebFiori\Mail;

/**
 * Interface that defines a contract for email delivery transports.
 *
 * Implementations can provide different delivery mechanisms such as
 * SMTP, API-based providers (SES, SendGrid), or test/null transports.
 *
 * @author Ibrahim
 */
interface TransportInterface {
    /**
     * Returns the name of the transport.
     *
     * @return string A string that identifies this transport (e.g. 'smtp', 'ses').
     */
    public function getName(): string;
    /**
     * Send an email message.
     *
     * @param Email $message The email to send.
     *
     * @throws Exceptions\SMTPException If sending fails.
     */
    public function send(Email $message): void;
}
