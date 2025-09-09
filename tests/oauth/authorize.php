<?php
require '../../vendor/autoload.php';

use WebFiori\OAuth\Providers\GoogleProvider;
use WebFiori\OAuth\Providers\MicrosoftProvider;
use WebFiori\OAuth\OAuth2Client;
use WebFiori\OAuth\Storage\FileTokenStorage;
use WebFiori\OAuth\TokenManager;

// Load providers configuration
if (!file_exists('providers.php')) {
    copy('providers-sample.php', 'providers.php');
}
$config = require 'providers.php';
$provider = $_GET['provider'] ?? null;

if (!$provider || !isset($config[$provider])) {
    die('Invalid or missing provider parameter');
}

$providerConfig = $config[$provider];

// Create provider instance
switch ($provider) {
    case 'microsoft':
        $oauthProvider = new MicrosoftProvider(
            $providerConfig['client_id'],
            $providerConfig['client_secret'],
            $providerConfig['redirect_uri'],
            $providerConfig['tenant']
        );
        $scopes = ['openid', 'offline_access', 'SMTP.Send'];
        break;
    case 'google':
        $oauthProvider = new GoogleProvider(
            $providerConfig['client_id'],
            $providerConfig['client_secret'],
            $providerConfig['redirect_uri']
        );
        $scopes = ['https://mail.google.com/'];
        break;
    default:
        die('Unsupported provider');
}

$client = new OAuth2Client($oauthProvider);
$tokensManager = new TokenManager(new FileTokenStorage(__DIR__ . '/tokens'));

// Handle authorization code exchange
if (isset($_GET['code'])) {
    try {
        $tokens = $client->exchangeCodeForToken($_GET['code']);
        $tokensManager->store($provider, $tokens);
        echo "Authorization successful! Tokens stored for provider: $provider<br>";
        echo "Access Token: " . $tokens['access_token']. "<br>";
    } catch (Exception $e) {
        echo "Error exchanging code: " . $e->getMessage();
    }
} else {
    // Generate authorization URL
    $state = bin2hex(random_bytes(16));
    $authUrl = $client->getAuthorizationUrl($scopes, $state);
    echo "<h2>OAuth Authorization for $provider</h2>";
    echo "<a href='$authUrl' target='_blank'>Click here to authorize</a>";
}
