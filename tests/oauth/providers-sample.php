<?php
/**
 * Sample OAuth providers configuration.
 * Copy this file to providers.php and update with your credentials.
 */

return [
    'microsoft' => [
        'client_id' => getenv('MICROSOFT_CLIENT_ID') ?: 'your-microsoft-client-id',
        'client_secret' => getenv('MICROSOFT_CLIENT_SECRET') ?: 'your-microsoft-client-secret',
        'redirect_uri' => getenv('MICROSOFT_REDIRECT_URI') ?: 'http://localhost:8989/authorize.php?provider=microsoft',
        'tenant' => getenv('MICROSOFT_TENANT') ?: 'common'
    ],
    'google' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: 'your-google-client-id',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: 'your-google-client-secret',
        'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost:8989/authorize.php?provider=google'
    ]
];
