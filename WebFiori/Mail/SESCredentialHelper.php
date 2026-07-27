<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Mail;

/**
 * Utility class for deriving Amazon SES SMTP credentials from IAM keys.
 *
 * Amazon SES SMTP requires a specially derived password — you cannot use
 * your IAM Secret Access Key directly. This helper performs the AWS
 * Signature Version 4 key derivation documented at:
 * https://docs.aws.amazon.com/ses/latest/dg/smtp-credentials.html
 *
 * The derived password is stable for a given Secret Access Key and region,
 * so it can be computed once and stored. If you rotate your IAM key, call
 * deriveSmtpPassword() again with the new Secret Access Key.
 *
 * ## Usage
 *
 * ```php
 * use WebFiori\Mail\SESCredentialHelper;
 * use WebFiori\Mail\AccountOption;
 * use WebFiori\Mail\SMTPAccount;
 *
 * $region = 'us-east-1';
 *
 * $account = new SMTPAccount([
 *     AccountOption::SERVER_ADDRESS => "email-smtp.{$region}.amazonaws.com",
 *     AccountOption::PORT           => 587,
 *     AccountOption::USERNAME       => 'AKIAIOSFODNN7EXAMPLE',
 *     AccountOption::PASSWORD       => SESCredentialHelper::deriveSmtpPassword(
 *                                          'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
 *                                          $region
 *                                      ),
 *     AccountOption::SENDER_ADDRESS => 'sender@verified-domain.com',
 *     AccountOption::SENDER_NAME    => 'My App',
 * ]);
 * ```
 *
 * @author Ibrahim
 */
class SESCredentialHelper {
    /**
     * The fixed message signed at each derivation step.
     * AWS uses "SendRawEmail" as the terminal message.
     */
    private const MESSAGE = 'SendRawEmail';
    /**
     * Version byte prepended to the derived key per AWS specification.
     */
    private const VERSION_BYTE = "\x04";

    /**
     * Derives the SMTP password for Amazon SES from an IAM Secret Access Key.
     *
     * The derivation follows the AWS SES SMTP credentials algorithm:
     * Base64( 0x04 || HMAC-SHA256("SendRawEmail",
     *     HMAC-SHA256("aws4_request",
     *         HMAC-SHA256("ses",
     *             HMAC-SHA256(region,
     *                 HMAC-SHA256("AWS4" + secretKey))))) )
     *
     * @param string $secretAccessKey The IAM Secret Access Key (not the Access Key ID).
     * @param string $region          The AWS region of your SES endpoint (e.g. 'us-east-1').
     *                                Defaults to 'us-east-1'.
     *
     * @return string The derived SMTP password, ready to use with AccountOption::PASSWORD.
     *
     * @throws \InvalidArgumentException If either argument is empty.
     */
    public static function deriveSmtpPassword(
        string $secretAccessKey,
        string $region = 'us-east-1'
    ): string {
        if (strlen(trim($secretAccessKey)) === 0) {
            throw new \InvalidArgumentException('secretAccessKey cannot be empty.');
        }

        if (strlen(trim($region)) === 0) {
            throw new \InvalidArgumentException('region cannot be empty.');
        }

        $kSecret = hash_hmac('sha256', 'AWS4'.$secretAccessKey, 'AWS4_request', true);
        $kDate = hash_hmac('sha256', $region,        $kSecret,  true);
        $kRegion = hash_hmac('sha256', 'ses',          $kDate,    true);
        $kService = hash_hmac('sha256', 'aws4_request', $kRegion,  true);
        $kSigning = hash_hmac('sha256', self::MESSAGE,  $kService, true);

        return base64_encode(self::VERSION_BYTE.$kSigning);
    }

    /**
     * Returns the SMTP endpoint hostname for a given AWS region.
     *
     * @param string $region The AWS region (e.g. 'us-east-1', 'eu-west-1').
     *
     * @return string The SES SMTP endpoint hostname.
     */
    public static function smtpEndpoint(string $region = 'us-east-1'): string {
        return "email-smtp.{$region}.amazonaws.com";
    }
}
