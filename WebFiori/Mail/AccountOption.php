<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2024-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Mail;

/**
 * A class that holds constants that represents SMTP account options.
 *
 * @author Ibrahim
 */
class AccountOption {
    /**
     * An option which is used to set OAuth access token.
     */
    const ACCESS_TOKEN = 'access-token';
    /**
     * An option which is used to allow self-signed SSL/TLS certificates.
     *
     * When set to true, connections to servers presenting self-signed certificates
     * will succeed even though the certificate was not signed by a trusted CA.
     * Peer verification (verify_peer, verify_peer_name) still applies.
     *
     * Defaults to false. Only set this to true for internal/development servers
     * with self-signed certificates.
     */
    const ALLOW_SELF_SIGNED = 'allow-self-signed';
    /**
     * An option which is used to set a unique name for the account.
     */
    const NAME = 'account-name';
    /**
     * An option which is used to enable or disable SSL/TLS peer verification.
     *
     * When set to false, the SSL certificate presented by the server will not
     * be verified. This disables both verify_peer and verify_peer_name, making
     * the connection vulnerable to man-in-the-middle attacks.
     *
     * Defaults to true. Only set this to false in controlled environments
     * where SSL verification is not possible.
     */
    const VERIFY_SSL = 'verify-ssl';
    /**
     * An option which is used to set the password of the account.
     */
    const PASSWORD = 'pass';
    /**
     * An option which is used to set SMTP server port.
     */
    const PORT = 'port';
    /**
     * An option which is used to set the address that will appear when the 
     * message is sent. Usually, it is the same as the username.
     */
    const SENDER_ADDRESS = 'sender-address';
    /**
     * An option which is used to set the name of the sender that will appear when the 
     * message is sent.
     */
    const SENDER_NAME = 'sender-name';
    /**
     * An option which is used to set the address of SMTP server.
     */
    const SERVER_ADDRESS = 'server-address';
    /**
     * An option which is used to set the username at which it is used to log in to SMTP server.
     */
    const USERNAME = 'user';
}
