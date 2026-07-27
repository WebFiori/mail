# OAuth Usage Examples

This folder demonstrates how to use OAuth2 and credential-based authentication
with WebFiori Mailer for enhanced security.

## Examples

### 🔐 microsoft-oauth.php
Sends email via Microsoft 365 / Outlook using SMTP XOAUTH2 with the built-in
`MicrosoftOAuthProvider`:
- No passwords stored — tokens are acquired and cached automatically
- Uses the Client Credentials flow (application-level, no user interaction)
- Token refreshed lazily just before each send

### 🔐 gmail-oauth.php
Manual OAuth token example for Gmail (legacy approach, superseded by
`GoogleOAuthProvider` — see issue #68).

### ☁️ ses-smtp.php
Sends email via Amazon SES SMTP using `SESCredentialHelper`:
- Derives the SES SMTP password from an IAM Secret Access Key
- Uses standard `AUTH LOGIN` — no OAuth involved
- No new transport or provider needed

---

## Microsoft OAuth Setup

See [docs/SMTP-OAuth-Setup.md](../../docs/SMTP-OAuth-Setup.md) for the full
Azure configuration walkthrough. Summary:

1. Register an app in **Microsoft Entra ID** (App registrations)
2. Add API permission: **Office 365 Exchange Online → SMTP.SendAsApp** (Application)
3. Grant admin consent
4. Create a **client secret** (Certificates & secrets)
5. Register the service principal in Exchange Online (`New-ServicePrincipal`)
6. Grant mailbox access (`Add-MailboxPermission`)

### Configuration

```php
use WebFiori\Mail\MicrosoftOAuthProvider;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;

$provider = new MicrosoftOAuthProvider(
    tenantId:     getenv('SMTP_TENANT_ID'),
    clientId:     getenv('SMTP_CLIENT_ID'),
    clientSecret: getenv('SMTP_CLIENT_SECRET')
);

$account = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.office365.com',
    AccountOption::PORT           => 587,
    AccountOption::USERNAME       => getenv('SMTP_USERNAME'),
    AccountOption::SENDER_ADDRESS => getenv('SMTP_USERNAME'),
    AccountOption::SENDER_NAME    => 'My App',
]);
$account->setTokenProvider($provider);
```

### Token caching

The provider caches the token in memory with a 5-minute expiry buffer.
Multiple emails sent from the same `$provider` instance in the same process
reuse the token — no extra requests to Microsoft.

---

## Amazon SES SMTP Setup

SES SMTP uses IAM credentials, not OAuth. `SESCredentialHelper` derives
the required SMTP password from your IAM Secret Access Key.

```php
use WebFiori\Mail\SESCredentialHelper;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;

$region = 'us-east-1';

$account = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => SESCredentialHelper::smtpEndpoint($region),
    AccountOption::PORT           => 587,
    AccountOption::USERNAME       => getenv('AWS_ACCESS_KEY_ID'),
    AccountOption::PASSWORD       => SESCredentialHelper::deriveSmtpPassword(
                                         getenv('AWS_SECRET_ACCESS_KEY'),
                                         $region
                                     ),
    AccountOption::SENDER_ADDRESS => 'sender@verified-domain.com',
    AccountOption::SENDER_NAME    => 'My App',
]);
```

No provider or special transport needed — SES SMTP uses standard `AUTH LOGIN`.

### Storing the derived password

The derivation is deterministic. You can pre-compute and store it:

```bash
# In a setup script (not in your application boot path):
php -r "
require 'vendor/autoload.php';
echo WebFiori\Mail\SESCredentialHelper::deriveSmtpPassword(
    getenv('AWS_SECRET_ACCESS_KEY'), 'us-east-1'
);
"
# → store the output as SES_SMTP_PASSWORD in your secrets manager
```

Then in production:

```php
AccountOption::PASSWORD => getenv('SES_SMTP_PASSWORD')
```

---

## OAuthTokenProvider Interface

Both `MicrosoftOAuthProvider` and `GoogleOAuthProvider` (#68) implement the
`OAuthTokenProvider` interface. You can implement it for any custom provider:

```php
use WebFiori\Mail\OAuthTokenProvider;

class MyCustomProvider implements OAuthTokenProvider {
    public function getToken(): string {
        // fetch, cache and return your access token
    }
}

$account->setTokenProvider(new MyCustomProvider());
```

The token is fetched **lazily** — `getToken()` is called just before SMTP
authentication, not at account construction time. This ensures the token is
always fresh regardless of how long the process has been running.

---

## Running the Examples

```bash
cd examples/oauth-usage

# Microsoft OAuth
SMTP_TENANT_ID=... SMTP_CLIENT_ID=... SMTP_CLIENT_SECRET=... SMTP_USERNAME=... \
    php microsoft-oauth.php

# Amazon SES
AWS_REGION=us-east-1 AWS_ACCESS_KEY_ID=... AWS_SECRET_ACCESS_KEY=... \
    php ses-smtp.php
```
