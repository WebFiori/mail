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
 * Interface for OAuth2 token providers used with SMTP XOAUTH2 authentication.
 *
 * Implementations are responsible for acquiring, caching, and refreshing
 * access tokens. The library calls getToken() lazily just before each send,
 * ensuring the token is always fresh regardless of how long the process runs.
 *
 * Example implementation for a custom provider:
 *
 * ```php
 * class MyProvider implements OAuthTokenProvider {
 *     public function getToken(): string {
 *         // fetch or return cached token
 *     }
 * }
 *
 * $account->setTokenProvider(new MyProvider());
 * ```
 *
 * @see MicrosoftOAuthProvider Built-in provider for Microsoft 365 / Outlook
 * @see GoogleOAuthProvider    Built-in provider for Gmail / Google Workspace
 *
 * @author Ibrahim
 */
interface OAuthTokenProvider {
    /**
     * Returns a valid OAuth2 access token.
     *
     * Implementations must handle token caching and refresh internally.
     * The method may make a network request if the cached token is expired
     * or not yet acquired.
     *
     * @return string A valid access token suitable for SMTP XOAUTH2 authentication.
     *
     * @throws \RuntimeException If the token cannot be acquired.
     */
    public function getToken(): string;
}
