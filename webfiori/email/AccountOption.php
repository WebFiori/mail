<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2024 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\email;

/**
 * A class that holds constants that represents SMTP account options.
 *
 * @author Ibrahim
 */
class AccountOption {
    /**
     * An option which is used to set the address of SMTP server.
     */
    const SERVER_ADDRESS = 'server-address';
    /**
     * An option which is used to set SMTP server port.
     */
    const PORT = 'port';
    /**
     * An option which is used to set the username at which it is used to log in to SMTP server.
     */
    const USERNAME = 'user';
    /**
     * An option which is used to set the password of the account.
     */
    const PASSWORD = 'pass';
    /**
     * An option which is used to set the name of the sender that will appear when the 
     * message is sent.
     */
    const SENDER_NAME = 'sender-name';
    /**
     * An option which is used to set the address that will appear when the 
     * message is sent. Usually, it is the same as the username.
     */
    const SENDER_ADDRESS = 'sender-address';
    /**
     * An option which is used to set a unique name for the account.
     */
    const NAME = 'account-name';
}
