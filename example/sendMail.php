<?php

require '../vendor/autoload.php';

use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;

$config = require '../config/accounts.php';
//First, create new SMTP account that holds SMTP connection information.
$smtp = new SMTPAccount(
    $config['other-smtp-1']);

//Second, create your actual email. using the account that was just created to
//send messages.
$email = new Email($smtp);

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

echo 'sent';
