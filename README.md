# WebFiori Mailer 
Sockets-based library for sending HTML email messages.


<p align="center">
  <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php81.yml">
    <img src="https://github.com/WebFiori/mail/workflows/Build%20PHP%208.1/badge.svg?branch=main">
  </a>
  <a href="https://codecov.io/gh/WebFiori/mail">
    <img src="https://codecov.io/gh/WebFiori/mail/branch/main/graph/badge.svg" />
  </a>
  <a href="https://sonarcloud.io/dashboard?id=WebFiori_mail">
      <img src="https://sonarcloud.io/api/project_badges/measure?project=WebFiori_mail&metric=alert_status" />
  </a>
  <a href="https://github.com/WebFiori/mail/releases">
      <img src="https://img.shields.io/github/release/WebFiori/mail.svg?label=latest" />
  </a>
  <a href="https://packagist.org/packages/webfiori/mailer">
    <img src="https://img.shields.io/packagist/dt/webfiori/mailer?color=light-green">
  </a>
</p>

## Supported PHP Versions
| Build Status |
|:-----------:|
|<a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php70.yml"><img src="https://github.com/WebFiori/mail/workflows/Build%20PHP%207.0/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php71.yml"><img src="https://github.com/WebFiori/mail/workflows/Build%20PHP%207.1/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php72.yml"><img src="https://github.com/WebFiori/mail/workflows/Build%20PHP%207.2/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php73.yml"><img src="https://github.com/WebFiori/mail/workflows/Build%20PHP%207.3/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php74.yml"><img src="https://github.com/WebFiori/mail/workflows/Build%20PHP%207.4/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php80.yml"><img src="https://github.com/WebFiori/mail/workflows/Build%20PHP%208.0/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php81.yml"><img src="https://github.com/WebFiori/mail/workflows/Build%20PHP%208.1/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php82.yml"><img src="https://github.com/WebFiori/mail/workflows/Build%20PHP%208.2/badge.svg?branch=main"></a><br>(dev)|

## Usage

### SMTP Server Connection

The first step in sending a message is to have SMTP server information including its address, port, a username and a password. Once this info is available, then we use the class `SMTPAccount` to keep track of them in the code:

``` php
<?php
require '../vendor/autoload.php';

use webfiori\email\SMTPAccount;

//First, create new SMTP account that holds SMTP connection information.
$smtp = new SMTPAccount([
    'port' => 465,
    //Replace server address with your mail server address
    'server-address' => 'mail.example.com',
    //Replace server username with your mail server username
    'user' => 'test@example.com',
    'pass' => 'KnvcbxFYCz77',
    'sender-name' => 'Ibrahim',
    //Replace sender address with your mail server sender address
    'sender-address' => 'test@example.com',
    'account-name' => 'no-reply'
]);
```

## Creating Email Message

After having the account information, an instance of the class `EmailMessage` can be created. The class will represent the acctuall message that will be send.

``` php
require '../vendor/autoload.php';

use webfiori\email\SMTPAccount;
use webfiori\email\EmailMessage;

//First, create new SMTP account that holds SMTP connection information.
$smtp = new SMTPAccount([
    'port' => 465,
    //Replace server address with your mail server address
    'server-address' => 'mail.example.com',
    //Replace server username with your mail server username
    'user' => 'test@example.com',
    'pass' => 'KnvcbxFYCz77',
    'sender-name' => 'Ibrahim',
    //Replace sender address with your mail server sender address
    'sender-address' => 'test@example.com',
    'account-name' => 'no-reply'
]);

//Second, create your actual email. using the account that was just created to
//send messages.
$email = new EmailMessage($smtp);
```

Once we have the instance, we can do many things with it.

``` php
//Set subject
$email->setSubject('Hello World From PHP 😀');

//Optionally, set priority
$email->setPriority(1);

//Specify who will receive the message
$email->addTo('super-megaman-x@outlook.com');

//Add optional attachments
$email->addAttachment(__DIR__.DIRECTORY_SEPARATOR.'AttachmentImg.png');

//Build your HTML Message
$div = $email->insert('div');
$div->addChild('p')->text('Hello World Message');
$div->addChild('p', [
    'style' => [
        'font-weight' => 'bold',
        'color' => 'red'
    ]
])->text('This is just a test message.');

//Finally, send.
$email->send();
```
