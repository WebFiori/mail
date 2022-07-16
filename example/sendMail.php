<?php
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