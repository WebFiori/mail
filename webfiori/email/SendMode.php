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
 * Class that holds the constants used to set sending mode of an email message
 * instance.
 *
 * @author Ibrahim
 */
class SendMode {
    /**
     * The default mode. Used to send the message to its recipients. 
     */
    const PROD = 'send';
    /**
     * This mode indicates that the message will be sent to a set of specified
     * addresses as test including meta-data of the message. Used to mimic actual
     * process of sending the message (similar to staging or QA env).
     */
    const TEST_SEND = 'test_send';
    /**
     * This mode indicates that the message will be stored as HTML in specific
     * path.
     */
    const TEST_STORE = 'store';
}
