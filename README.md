# WebFiori Mailer 
A basic library for sending HTML based emails using PHP. 


<p align="center">
  <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php83.yml">
    <img src="https://github.com/WebFiori/mail/actions/workflows/php83.yml/badge.svg?branch=main">
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
|                                                                                        Build Status                                                                                        |
|:------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php70.yml"><img src="https://github.com/WebFiori/mail/actions/workflows/php70.yml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php71.yml"><img src="https://github.com/WebFiori/mail/actions/workflows/php71.yml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php72.yml"><img src="https://github.com/WebFiori/mail/actions/workflows/php72.yml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php73.yml"><img src="https://github.com/WebFiori/mail/actions/workflows/php73.yml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php74.yml"><img src="https://github.com/WebFiori/mail/actions/workflows/php74.yml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php80.yml"><img src="https://github.com/WebFiori/mail/actions/workflows/php80.yml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php81.yml"><img src="https://github.com/WebFiori/mail/actions/workflows/php81.yml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php82.yml"><img src="https://github.com/WebFiori/mail/actions/workflows/php82.yml/badge.svg?branch=main"></a>  |
| <a target="_blank" href="https://github.com/WebFiori/mail/actions/workflows/php83.yml"><img src="https://github.com/WebFiori/mail/actions/workflows/php83.yml/badge.svg?branch=main"></a>  |

## In This Page:
* [Usage](#usage)
  * [Basic Usage](#basic-usage)
    * [Connecting to SMTP Server](#connecting-to-smtp-server)
    * [Creating Email Message](#creating-email-message)
    * [Setting Email Subject](#setting-email-subject)
    * [Adding a Recipient](#adding-a-recipient)
    * [Writing Some Text](#writing-some-text)
    * [Sending The Message](#sending-the-message)
    * [All Togather](#all-togather)
* [Before Render Callback](#before-render-callback)
* [After Render Callback](#after-render-callback)
* [Accessing SMTP Log](#accessing-smtp-log)
* [Storing Email](#storing-email)
* [Setup Testing](#setup-testing)

## Usage

### Basic Usage

This section describes most basic use case of the library. It shows how to connect to SMTP server, writing a message and sending it to specific address.

#### Connecting to SMTP Server

Connection information are represented using an instance of the class [`webfiori\email\SMTPAccount`](https://github.com/WebFiori/mail/blob/main/webfiori/email/SMTPAccount.php).
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

#### Creating Email Message

After having SMTP connection information, an instance of the class [`webfiori\email\Email`](https://github.com/WebFiori/mail/blob/dev/webfiori/email/Email.php) can be created. The consructor of the class will accept one parameter which is the connection that will be used to connect to SMTP server.

``` php
//Second, create your actual email. using the account that was just created to
//send messages.
$email = new EmailMessage($smtp);
```
#### Setting Email Subject

To set the subject of the message, the method `Email::setSubject()` can be used as follows:
``` php
//Set subject
$email->setSubject('Hello World From PHP 😀');
```

#### Adding a Recipient

``` php
//Specify who will receive the message
$email->addTo('super-megaman-x@outlook.com');
```

#### Writing Some Text

The email messages which are created using the library are HTML based. They utilize the library [`webfiori\ui`](https://github.com/WebFiori/ui) to build the virtual DOM.

An HTML elemtnt can be inserted to the body of the message by using the method `Email::insert()`.

``` php

//Build your HTML Message
$div = $email->insert('div');
$div->addChild('p')->text('Hello World Message');
$div->addChild('p', [
    'style' => [
        'font-weight' => 'bold',
        'color' => 'red'
    ]
])->text('This is just a test message.');
```

#### Sending The Message

The final step is to send the message. This can be performed using the method `Email::send()`.

``` php
//Finally, send.
$email->send();
```

#### All Togather

When we put all the steps as one, we would have the following:

``` php
require '../vendor/autoload.php';

use webfiori\email\SMTPAccount;
use webfiori\email\EmailMessage;

$smtp = new SMTPAccount([
    'port' => 465,
    'server-address' => 'mail.example.com',
    'user' => 'test@example.com',
    'pass' => 'KnvcbxFYCz77',
    'sender-name' => 'Ibrahim',
    'sender-address' => 'test@example.com',
    'account-name' => 'no-reply'
]);

$email = new EmailMessage($smtp);

$email->setSubject('Hello World From PHP 😀');

$email->addTo('super-megaman-x@outlook.com');

$div = $email->insert('div');
$div->addChild('p')->text('Hello World Message');
$div->addChild('p', [
    'style' => [
        'font-weight' => 'bold',
        'color' => 'red'
    ]
])->text('This is just a test message.');

$email->send();
```
## Before Render Callback
## After Render Callback
## Accessing SMTP Log
## Storing Email

Since the emails which are constructed using the library are HTML based, they can be stored as HTML web pages. This feature is useful in case the developer would like to test a preview of final constructed email.

To store an email as HTML web page, the method `Email::storeEmail()` can be used as follows:

``` php
$m = new Email(new SMTPAccount([
    'port' => 465,
    'server-address' => 'mail.example.com',
    'user' => 'test@example.com',
    'pass' => 'KnvcbxFYCz77',
    'sender-name' => 'WebFiori',
    'sender-address' => 'test@example.com',
    'account-name' => 'no-reply'
]));
$m->setSubject('Test Ability to Store Email');
$m->addTo('ibx@example.com');
$m->insert('p')->text('Dear,')->setStyle([
    'font-weight' => 'bold',
    'font-size' => '15pt'
]);
$m->insert('p')->text('This email is just to inform you that you can store emails as web pages.');
$m->insert('p')->text('Regards,')->setStyle([
    'color' => 'green',
    'font-weight' => 'bold'
]);
$m->storeEmail('/path/to/email/file');
```

The call to the method `Email::storeEmail()` will do the following:

* Render the final email.
* Create a folder which has same subject as the email inside provided folder.
* Create HTML file which has the date and time as its name inside the folder.

The final output of the given code will be HTML web page that is similar to following image.

![image](https://github.com/WebFiori/mail/assets/12120015/abe81167-8743-4fd1-ab7a-c16d2bbd1411)

## Setup Testing

