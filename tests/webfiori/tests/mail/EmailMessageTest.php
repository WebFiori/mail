<?php
namespace webfiori\tests\mail;

use PHPUnit\Framework\TestCase;
use webfiori\email\EmailMessage;
use webfiori\email\SMTPAccount;
use webfiori\email\SMTPServer;
use webfiori\email\exceptions\SMTPException;
/**
 * A test class for testing the class 'webfiori\framework\mail\EmailMessage'.
 *
 * @author Ibrahim
 */
class EmailMessageTest extends TestCase {
    private $acc00 = [
        'port' => 587,
        'server-address' => 'outlook.office365.com',
        'user' => 'randomxyz@hotmail.com',
        'pass' => '???',
        'sender-name' => 'Ibrahim',
        'sender-address' => 'randomxyz@hotmail.com',
        'account-name' => 'no-reply'
    ];
    private $acc01 = [
        'port' => 465,
        'server-address' => 'mail.programmingacademia.com',
        'user' => 'test@programmingacademia.com',
        'pass' => 'KnvcbxFYCz77',
        'sender-name' => 'Ibrahim',
        'sender-address' => 'test@programmingacademia.com',
        'account-name' => 'no-reply2'
    ];
    private $acc02 = [
        'port' => 465,
        'server-address' => 'mail.programmingacademia.com',
        'user' => 'test@programmingacademia.com',
        'pass' => '2233',
        'sender-name' => 'Ibrahim',
        'sender-address' => 'test@programmingacademia.com',
        'account-name' => 'no-reply2'
    ];
    /**
     * @test
     */
    public function testAddReciver00() {
        $account = new SMTPAccount($this->acc00);
        $sm = new EmailMessage($account);
        $this->assertFalse($sm->addTo('', ''));
        $this->assertFalse($sm->addTo('', 'Hello'));
        $this->assertFalse($sm->addTo('', 'hello@web.com'));
    }
    /**
     * @test
     */
    public function testAddReciver01() {
        $account = new SMTPAccount($this->acc00);
        $sm = new EmailMessage($account);
        $this->assertTrue($sm->addTo('   hello@>hello.com ', '  <Hello'));
        $this->assertEquals('=?UTF-8?B?SGVsbG8=?= <hello@hello.com>',$sm->getToStr());
        $this->assertEquals('Hello',$sm->getTo()['hello@hello.com']);
        $this->assertTrue($sm->addTo('  hello@>hello.com  ', ' <Hello2 '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>',$sm->getToStr());
        $this->assertTrue($sm->addTo(' hello-9@>hello.com ', '  Hel>lo-9'));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>,=?UTF-8?B?SGVsbG8tOQ==?= <hello-9@hello.com>',$sm->getToStr());
    }
    /**
     * @test
     */
    public function testAddReciver02() {
        $account = new SMTPAccount($this->acc00);
        $sm = new EmailMessage($account);
        $this->assertTrue($sm->addCC(' hello@>hello.com   ', ' <Hello '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8=?= <hello@hello.com>',$sm->getCCStr());
        $this->assertEquals('Hello',$sm->getCC()['hello@hello.com']);
        $this->assertTrue($sm->addCC('  hello@>hello.com  ', ' <Hello2 '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>',$sm->getCCStr());
        $this->assertTrue($sm->addCC(' hello-9@>hello.com  ', ' Hel>lo-9 '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>,=?UTF-8?B?SGVsbG8tOQ==?= <hello-9@hello.com>',$sm->getCCStr());
    }
    /**
     * @test
     */
    public function testAddReciver03() {
        $account = new SMTPAccount($this->acc00);
        $sm = new EmailMessage($account);
        $this->assertTrue($sm->addBCC(' hello@>hello.com   ', '   <Hello',true,true));
        $this->assertEquals('=?UTF-8?B?SGVsbG8=?= <hello@hello.com>',$sm->getBCCStr());
        $this->assertEquals('Hello',$sm->getBCC()['hello@hello.com']);
        $this->assertTrue($sm->addBCC('hello@>hello.com  ', '  <Hello2  ',true,true));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>',$sm->getBCCStr());
        $this->assertTrue($sm->addBCC('hello-9@>hello.com   ', '  Hel>lo-9',true,true));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>,=?UTF-8?B?SGVsbG8tOQ==?= <hello-9@hello.com>',$sm->getBCCStr());
    }
    /**
     * @test
     */
    public function testConstructor00() {
        $account = new SMTPAccount($this->acc00);
        $sm = new EmailMessage($account);
        $this->assertEquals('',$sm->getSMTPServer()->getLastResponse());
        $this->assertSame(0,$sm->getSMTPServer()->getLastResponseCode());
        $this->assertSame(0,$sm->getPriority());
        $this->assertSame(5,$sm->getSMTPServer()->getTimeout());
    }
    /**
     * @test
     */
    public function testSetPriority00() {
        $account = new SMTPAccount($this->acc00);
        $sm = new EmailMessage($account);
        $sm->setPriority(-2);
        $this->assertSame(-1,$sm->getPriority());
        $sm->setPriority(100);
        $this->assertSame(1,$sm->getPriority());
        $sm->setPriority(33);
        $this->assertEquals(1, $sm->getPriority());
        $sm->setPriority(-26544);
        $this->assertSame(-1,$sm->getPriority());
        $sm->setPriority(26544);
        $this->assertSame(1,$sm->getPriority());
        $sm->setPriority(0);
        $this->assertSame(0,$sm->getPriority());
    }
    /**
     * @test
     */
    public function testSend00() {
        $message = new EmailMessage(new SMTPAccount($this->acc01));
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('ibinshikh@outlook.com');
        $message->addBeforeSend(function (EmailMessage $m, TestCase $c) {
            $c->assertTrue($m->addAttachment(__DIR__.DIRECTORY_SEPARATOR.'Attach00.txt'));
            $c->assertFalse($m->addAttachment('NotExtst.txt'));
            $c->assertFalse($m->addAttachment(new \webfiori\file\File(__DIR__.DIRECTORY_SEPARATOR.'not-exist.txt')));
            $c->assertTrue($m->addAttachment(new \webfiori\file\File(__DIR__.DIRECTORY_SEPARATOR.'favicon.png')));
        }, [$this]);
        $this->assertEquals('<!DOCTYPE html>'.SMTPServer::NL
                . '<html>'.SMTPServer::NL
                . '    <head>'.SMTPServer::NL
                . '        <title>'.SMTPServer::NL
                . '            Default'.SMTPServer::NL
                . '        </title>'.SMTPServer::NL
                . '        <meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">'.SMTPServer::NL
                . '    </head>'.SMTPServer::NL
                . '    <body itemscope itemtype="http://schema.org/WebPage">'.SMTPServer::NL
                . '        <p>'.SMTPServer::NL
                . '            Super test message.'.SMTPServer::NL
                . '        </p>'.SMTPServer::NL
                . '    </body>'.SMTPServer::NL
                . '</html>'.SMTPServer::NL, $message->getDocument()->toHTML());
        $message->addBeforeSend(function (EmailMessage $m, TestCase $c) {
            $c->assertEquals(2, count($m->getAttachments()));
        }, [$this]); 
        $message->send();
        $this->assertTrue(true);
    }
    /**
     * @test
     */
    public function testSend01() {
        $this->expectException(SMTPException::class);
        $this->expectExceptionMessage('Unable to login to SMTP server: 535 Incorrect authentication data');
        $message = new EmailMessage(new SMTPAccount($this->acc02));
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('ibinshikh@outlook.com');
        
        $message->send();
       
    }
}
