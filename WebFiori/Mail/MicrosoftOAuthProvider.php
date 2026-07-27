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
 * OAuth2 token provider for Microsoft 365 and Outlook.com via the
 * Client Credentials flow (RFC 6749 §4.4) against Microsoft Entra ID.
 *
 * Suitable for application-level SMTP sending where no user interaction
 * is required. Requires an App Registration in Microsoft Entra ID with
 * the SMTP.SendAsApp application permission granted and admin-consented.
 *
 * Tokens are cached in-memory and refreshed automatically when within
 * 5 minutes of expiry.
 *
 * ## Azure setup summary
 *
 * 1. Register an app in Entra ID (Azure Portal → App registrations)
 * 2. Add API permission: Office 365 Exchange Online → SMTP.SendAsApp (Application)
 * 3. Grant admin consent
 * 4. Create a client secret (Certificates & secrets)
 * 5. Register service principal in Exchange Online (New-ServicePrincipal)
 * 6. Grant mailbox access (Add-MailboxPermission)
 *
 * @see https://learn.microsoft.com/en-us/exchange/client-developer/legacy-protocols/how-to-authenticate-an-imap-pop-smtp-application-by-using-oauth
 *
 * @author Ibrahim
 */
class MicrosoftOAuthProvider implements OAuthTokenProvider {
    /**
     * Cached access token.
     *
     * @var string|null
     */
    private ?string $cachedToken = null;

    /**
     * Application (client) ID from the App Registration.
     *
     * @var string
     */
    private string $clientId;

    /**
     * Client secret value from the App Registration.
     *
     * @var string
     */
    private string $clientSecret;

    /**
     * Unix timestamp at which the cached token expires.
     *
     * @var int
     */
    private int $expiresAt = 0;

    /**
     * Microsoft Entra ID tenant ID (Directory ID).
     *
     * @var string
     */
    private string $tenantId;

    /**
     * Creates a new Microsoft OAuth provider.
     *
     * @param string $tenantId     Directory (tenant) ID from App Registration Overview.
     * @param string $clientId     Application (client) ID from App Registration Overview.
     * @param string $clientSecret Client secret Value (not the Secret ID).
     *
     * @throws \InvalidArgumentException If any argument is empty.
     */
    public function __construct(string $tenantId, string $clientId, string $clientSecret) {
        if (strlen(trim($tenantId)) === 0) {
            throw new \InvalidArgumentException('tenantId cannot be empty.');
        }

        if (strlen(trim($clientId)) === 0) {
            throw new \InvalidArgumentException('clientId cannot be empty.');
        }

        if (strlen(trim($clientSecret)) === 0) {
            throw new \InvalidArgumentException('clientSecret cannot be empty.');
        }

        $this->tenantId = trim($tenantId);
        $this->clientId = trim($clientId);
        $this->clientSecret = $clientSecret;
    }

    /**
     * Returns a valid access token, fetching a new one if the cached token
     * is expired or within 5 minutes of expiry.
     *
     * @return string The access token.
     *
     * @throws \RuntimeException If the token cannot be fetched.
     */
    public function getToken(): string {
        if ($this->cachedToken !== null && time() < ($this->expiresAt - 300)) {
            return $this->cachedToken;
        }

        return $this->fetchToken();
    }

    /**
     * Fetches a fresh token from the Microsoft token endpoint.
     *
     * @return string The new access token.
     *
     * @throws \RuntimeException If cURL fails or the response is not a 200 with an access_token.
     */
    private function fetchToken(): string {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('The cURL extension is required by MicrosoftOAuthProvider.');
        }

        $url = sprintf(
            'https://login.microsoftonline.com/%s/oauth2/v2.0/token',
            urlencode($this->tenantId)
        );

        $postFields = http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'https://outlook.office365.com/.default',
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException(
                'MicrosoftOAuthProvider: cURL request failed: '.$curlError
            );
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !isset($data['access_token'])) {
            $error = $data['error_description'] ?? $data['error'] ?? 'Unknown error';
            throw new \RuntimeException(
                sprintf('MicrosoftOAuthProvider: token request failed (HTTP %d): %s', $httpCode, $error)
            );
        }

        $this->cachedToken = $data['access_token'];
        $this->expiresAt = time() + (int) ($data['expires_in'] ?? 3600);

        return $this->cachedToken;
    }
}
