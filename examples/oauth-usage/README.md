# OAuth Usage Examples

This folder demonstrates how to use OAuth2 authentication with WebFiori Mailer for enhanced security.

## Examples

### 🔐 gmail-oauth.php
Shows OAuth2 integration with Gmail:
- Configure Gmail SMTP with access token
- Send emails without storing passwords
- Enhanced security for Gmail accounts

### 🔐 microsoft-oauth.php
Demonstrates Microsoft OAuth integration:
- Office 365 and Outlook.com compatibility
- Enterprise-grade authentication
- Multi-tenant application support

## OAuth2 Setup

### Gmail OAuth Setup

1. **Create Google Cloud Project**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project or select existing one

2. **Enable Gmail API**
   - Navigate to APIs & Services > Library
   - Search for "Gmail API" and enable it

3. **Create OAuth Credentials**
   - Go to APIs & Services > Credentials
   - Create OAuth 2.0 Client ID
   - Configure authorized redirect URIs

4. **Get Access Token**
   - Use OAuth flow to obtain access token
   - Store token securely in your application

### Microsoft OAuth Setup

1. **Register Application**
   - Go to [Azure Portal](https://portal.azure.com/)
   - Navigate to Azure Active Directory > App registrations
   - Register new application

2. **Configure Permissions**
   - Add Microsoft Graph permissions
   - Grant `Mail.Send` permission
   - Admin consent may be required

3. **Get Access Token**
   - Use OAuth 2.0 authorization code flow
   - Exchange code for access token

## Configuration

### Gmail OAuth Configuration
```php
$gmailAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp.gmail.com',
    AccountOption::PORT => 587,
    AccountOption::USERNAME => 'your-email@gmail.com',
    AccountOption::ACCESS_TOKEN => 'your-oauth-access-token',
    AccountOption::SENDER_ADDRESS => 'your-email@gmail.com',
    AccountOption::SENDER_NAME => 'Your Name',
    AccountOption::NAME => 'gmail-oauth'
]);
```

### Microsoft OAuth Configuration
```php
$microsoftAccount = new SMTPAccount([
    AccountOption::SERVER_ADDRESS => 'smtp-mail.outlook.com',
    AccountOption::PORT => 587,
    AccountOption::USERNAME => 'your-email@outlook.com',
    AccountOption::ACCESS_TOKEN => 'your-microsoft-oauth-token',
    AccountOption::SENDER_ADDRESS => 'your-email@outlook.com',
    AccountOption::SENDER_NAME => 'Your Name',
    AccountOption::NAME => 'microsoft-oauth'
]);
```

## Token Management

### Access Token Lifecycle
- **Expiration**: OAuth tokens typically expire (1 hour for Google, variable for Microsoft)
- **Refresh**: Use refresh tokens to obtain new access tokens
- **Storage**: Store tokens securely (encrypted database, secure environment variables)

### Best Practices
- Never store tokens in plain text
- Implement token refresh logic
- Handle token expiration gracefully
- Use secure storage mechanisms

## Running the Examples

```bash
# Navigate to oauth-usage directory
cd examples/oauth-usage

# Run Gmail OAuth example
php gmail-oauth.php

# Run Microsoft OAuth example
php microsoft-oauth.php
```

## Security Benefits

### OAuth2 Advantages
- **No Password Storage**: Eliminates need to store email passwords
- **Granular Permissions**: Request only necessary permissions
- **Revocable Access**: Tokens can be revoked without password changes
- **Audit Trail**: Better tracking of application access

### Enterprise Features
- **Multi-tenant Support**: Single application, multiple organizations
- **Conditional Access**: Integration with enterprise security policies
- **Compliance**: Meets enterprise security requirements

## Troubleshooting

### Common Issues
- **Invalid Token**: Check token expiration and refresh if needed
- **Insufficient Permissions**: Verify OAuth scopes are correctly configured
- **SMTP Errors**: Ensure OAuth token has mail sending permissions

### Error Handling
Always implement proper error handling for OAuth-related failures:

```php
try {
    $email->send();
} catch (SMTPException $e) {
    if (strpos($e->getMessage(), 'authentication') !== false) {
        // Handle OAuth token issues
        refreshAccessToken();
    }
}
```
