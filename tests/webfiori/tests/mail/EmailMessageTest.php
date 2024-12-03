<?php
namespace webfiori\tests\mail;

use PHPUnit\Framework\TestCase;
use webfiori\email\AccountOption;
use webfiori\email\Email;
use webfiori\email\exceptions\SMTPException;
use webfiori\email\SendMode;
use webfiori\email\SMTPAccount;
use webfiori\email\SMTPServer;
use webfiori\file\exceptions\FileException;
use webfiori\file\File;
/**
 * A test class for testing the class 'webfiori\framework\mail\EmailMessage'.
 *
 * @author Ibrahim
 */
class EmailMessageTest extends TestCase {
    private $acc00 = [
        AccountOption::PORT => 587,
        AccountOption::SERVER_ADDRESS => 'outlook.office365.com',
        AccountOption::USERNAME => 'randomxyz@hotmail.com',
        AccountOption::PASSWORD => '???',
        AccountOption::SENDER_NAME => 'Ibrahim',
        AccountOption::SENDER_ADDRESS => 'randomxyz@hotmail.com',
        AccountOption::NAME => 'no-reply'
    ];
    private $acc01 = [
        AccountOption::PORT => 465,
        AccountOption::SERVER_ADDRESS => 'mail.programmingacademia.com',
        AccountOption::USERNAME => 'test@programmingacademia.com',
        AccountOption::PASSWORD => 'KnvcbxFYCz77',
        AccountOption::SENDER_NAME => 'Ibrahim',
        AccountOption::SENDER_ADDRESS => 'test@programmingacademia.com',
        AccountOption::NAME => 'no-reply2'
    ];
    private $acc02 = [
        AccountOption::PORT => 465,
        AccountOption::SERVER_ADDRESS => 'mail.programmingacademia.com',
        AccountOption::USERNAME => 'test@programmingacademia.com',
        AccountOption::PASSWORD => '2233',
        AccountOption::SENDER_NAME => 'Ibrahim',
        AccountOption::SENDER_ADDRESS => 'test@programmingacademia.com',
        AccountOption::NAME => 'no-reply2'
    ];
    /**
     * @test
     */
    public function testAddReciver00() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $this->assertFalse($sm->addTo('', ''));
        $this->assertFalse($sm->addTo('', 'Hello'));
        $this->assertFalse($sm->addTo('', 'hello@web.com'));
    }
    /**
     * @test
     */
    public function testAddReciver01() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $this->assertTrue($sm->addTo('   hello@>hello.com ', '  <Hello'));
        $this->assertEquals('=?UTF-8?B?SGVsbG8=?= <hello@hello.com>',$sm->getToStr());
        $this->assertEquals('Hello',$sm->getTo()['hello@hello.com']);
        $this->assertTrue($sm->addTo('  hello@>hello.com  ', ' <Hello2 '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>',$sm->getToStr());
        $this->assertTrue($sm->addTo(' hello-9@>hello.com ', '  Hel>lo-9'));
        $this->assertTrue($sm->addTo('   hello@s.com '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>,=?UTF-8?B?SGVsbG8tOQ==?= <hello-9@hello.com>,=?UTF-8?B?aGVsbG9Acy5jb20=?= <hello@s.com>',$sm->getToStr());
    }
    /**
     * @test
     */
    public function testAddReciver02() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $this->assertTrue($sm->addCC(' hello@>hello.com   ', ' <Hello '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8=?= <hello@hello.com>',$sm->getCCStr());
        $this->assertEquals('Hello',$sm->getCC()['hello@hello.com']);
        $this->assertTrue($sm->addCC('  hello@>hello.com  ', ' <Hello2 '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>',$sm->getCCStr());
        $this->assertTrue($sm->addCC(' hello-9@>hello.com  ', ' Hel>lo-9 '));
        $this->assertTrue($sm->addCC(' hello-9@x.com  '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>,=?UTF-8?B?SGVsbG8tOQ==?= <hello-9@hello.com>,=?UTF-8?B?aGVsbG8tOUB4LmNvbQ==?= <hello-9@x.com>',$sm->getCCStr());
    }
    /**
     * @test
     */
    public function testAddReciver03() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $this->assertTrue($sm->addBCC(' hello@>hello.com   ', '   <Hello'));
        $this->assertEquals('=?UTF-8?B?SGVsbG8=?= <hello@hello.com>',$sm->getBCCStr());
        $this->assertEquals('Hello',$sm->getBCC()['hello@hello.com']);
        $this->assertTrue($sm->addBCC('hello@>hello.com  ', '  <Hello2  '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>',$sm->getBCCStr());
        $this->assertTrue($sm->addBCC('hello-9@>hello.com   ', '  Hel>lo-9'));
        $this->assertTrue($sm->addBCC('hello-9@>hello.com   '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>,=?UTF-8?B?aGVsbG8tOUBoZWxsby5jb20=?= <hello-9@hello.com>',$sm->getBCCStr());
    }
    /**
     * @test
     */
    public function testAddReciver04() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $sm->addRecipients(['hello@hello.com']);
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>',$sm->getToStr());
        $this->assertEquals('hello@hello.com',$sm->getTo()['hello@hello.com']);
        $sm->addRecipients(['hello2@hello.com' => 'Hello2']);
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>,=?UTF-8?B?SGVsbG8y?= <hello2@hello.com>',$sm->getToStr());
        $this->assertEquals('Hello2',$sm->getTo()['hello2@hello.com']);
    }
    /**
     * @test
     */
    public function testAddReciver05() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $sm->addRecipients(['hello@hello.com'], 'cc');
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>',$sm->getCCStr());
        $this->assertEquals('hello@hello.com',$sm->getCC()['hello@hello.com']);
        $sm->addRecipients(['hello2@hello.com' => 'Hello2'], 'cc');
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>,=?UTF-8?B?SGVsbG8y?= <hello2@hello.com>',$sm->getCCStr());
        $this->assertEquals('Hello2',$sm->getCC()['hello2@hello.com']);
    }
    /**
     * @test
     */
    public function testAddReciver06() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $sm->addRecipients(['hello@hello.com'], 'Bcc');
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>',$sm->getBCCStr());
        $this->assertEquals('hello@hello.com',$sm->getBCC()['hello@hello.com']);
        $sm->addRecipients(['hello2@hello.com' => 'Hello2'], 'BCc');
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>,=?UTF-8?B?SGVsbG8y?= <hello2@hello.com>',$sm->getBCCStr());
        $this->assertEquals('Hello2',$sm->getBCC()['hello2@hello.com']);
    }
    /**
     * @test
     */
    public function testBeforeSend00() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $sm->addBeforeSend(function (Email $e, string $name) {
            $e->insert('p')->setID('hello-parag')->text('Hello '.$name);
        }, ['Ibrahim']);
        $sm->invokeBeforeSend();
        $sm->invokeBeforeSend();
        $this->assertEquals('Hello Ibrahim', $sm->getChildByID('hello-parag')->getChild(0)->getText());
        $this->assertEquals("<!DOCTYPE html>\r\n"
                . "<html>\r\n"
                . "    <head>\r\n"
                . "        <title>\r\n"
                . "            Default\r\n"
                . "        </title>\r\n"
                . "        <meta name=viewport content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\">\r\n"
                . "    </head>\r\n"
                . "    <body itemscope itemtype=\"http://schema.org/WebPage\">\r\n"
                . "        <p id=\"hello-parag\">\r\n"
                . "            Hello Ibrahim\r\n"
                . "        </p>\r\n"
                . "    </body>\r\n"
                . "</html>\r\n", $sm.'');
    }
    /**
     * @test
     */
    public function testSetLang00() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $this->assertEquals('', $sm->getLang());
        $sm->setLang();
        $this->assertEquals('EN', $sm->getLang());
        $sm->setLang('aR');
        $this->assertEquals('AR', $sm->getLang());
        $sm->setLang('ggf');
        $this->assertEquals('AR', $sm->getLang());
    }
    /**
     * @test
     */
    public function testAfterSend00() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
        $sm->addAfterSend(function (Email $e, string $name) {
            $e->insert('p')->setID('hello-parag')->text('Hello '.$name);
        }, ['Ibrahim']);
        $sm->invokeAfterSend();
        $sm->invokeAfterSend();
        $this->assertEquals('Hello Ibrahim', $sm->getChildByID('hello-parag')->getChild(0)->getText());
        $this->assertEquals("<!DOCTYPE html>\r\n"
                . "<html>\r\n"
                . "    <head>\r\n"
                . "        <title>\r\n"
                . "            Default\r\n"
                . "        </title>\r\n"
                . "        <meta name=viewport content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\">\r\n"
                . "    </head>\r\n"
                . "    <body itemscope itemtype=\"http://schema.org/WebPage\">\r\n"
                . "        <p id=\"hello-parag\">\r\n"
                . "            Hello Ibrahim\r\n"
                . "        </p>\r\n"
                . "    </body>\r\n"
                . "</html>\r\n", $sm.'');
    }
    /**
     * @test
     */
    public function testConstructor00() {
        $account = new SMTPAccount($this->acc00);
        $sm = new Email($account);
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
        $sm = new Email($account);
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
        $message = new Email(new SMTPAccount($this->acc01));
        $this->assertEquals([
            'command' => '', 
            'code' => 0, 
            'message' => '', 
            'time' => ''
        ], $message->getSMTPServer()->getLastLogEntry());
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('ibinshikh@outlook.com');
        $message->addBeforeSend(function (Email $m, TestCase $c) {
            $c->assertTrue($m->addAttachment(__DIR__.DIRECTORY_SEPARATOR.'Attach00.txt'));
            $c->assertFalse($m->addAttachment('NotExtst.txt'));
            $c->assertFalse($m->addAttachment(new File(__DIR__.DIRECTORY_SEPARATOR.'not-exist.txt')));
            $c->assertTrue($m->addAttachment(new File(__DIR__.DIRECTORY_SEPARATOR.'favicon.png')));
            $c->assertFalse($m->addAttachment($c));
        }, [$this]);
        $this->assertEquals('<!DOCTYPE html>'.SMTPServer::NL
                . '<html>'.SMTPServer::NL
                . '    <head>'.SMTPServer::NL
                . '        <title>'.SMTPServer::NL
                . '            Test Email From WebFiori'.SMTPServer::NL
                . '        </title>'.SMTPServer::NL
                . '        <meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">'.SMTPServer::NL
                . '    </head>'.SMTPServer::NL
                . '    <body itemscope itemtype="http://schema.org/WebPage">'.SMTPServer::NL
                . '        <p>'.SMTPServer::NL
                . '            Super test message.'.SMTPServer::NL
                . '        </p>'.SMTPServer::NL
                . '    </body>'.SMTPServer::NL
                . '</html>'.SMTPServer::NL, $message->getDocument()->toHTML());
        $message->addBeforeSend(function (Email $m, TestCase $c) {
            $c->assertEquals(2, count($m->getAttachments()));
        }, [$this]); 
        $message->send();
        $this->assertEquals([
            'command' => 'QUIT',
            'code' => 221,
            'message' => '221 gator4189.hostgator.com closing connection',
            'time' => date('Y-m-d H:i:s'),
        ], $message->getSMTPServer()->getLastLogEntry());
        $this->assertTrue(true);
    }
    /**
     * @test
     */
    public function testSend01() {
        $this->expectException(SMTPException::class);
        $this->expectExceptionMessage('Unable to login to SMTP server: 535 Incorrect authentication data');
        $message = new Email(new SMTPAccount($this->acc02));
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('ibinshikh@outlook.com');
        
        $message->send();
       
    }
    /**
     * @test
     */
    public function testSend02() {
        $message = new Email(new SMTPAccount($this->acc02));
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('ibinshikh@outlook.com');
        try {
            $message->send();
        } catch (SMTPException $ex) {
            $this->assertEquals([
                'command' => 'MjIzMw==',
                'code' => 535,
                'message' => '535 Incorrect authentication data',
                'time' => date('Y-m-d H:i:s')
            ], $message->getSMTPServer()->getLastLogEntry());
            $this->assertEquals(7, count($ex->getLog()));
        }
       
    }
    /**
     * @test
     */
    public function testSend03() {
        $this->expectException(SMTPException::class);
        $this->expectExceptionMessage('Message was already sent.');
        $message = new Email(new SMTPAccount($this->acc01));

        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('ibinshikh@outlook.com');
        $message->addBeforeSend(function (Email $m, TestCase $c) {
            $c->assertTrue($m->addAttachment(__DIR__.DIRECTORY_SEPARATOR.'Attach00.txt'));
            $c->assertFalse($m->addAttachment('NotExtst.txt'));
            $c->assertFalse($m->addAttachment(new File(__DIR__.DIRECTORY_SEPARATOR.'not-exist.txt')));
            $c->assertTrue($m->addAttachment(new File(__DIR__.DIRECTORY_SEPARATOR.'favicon.png')));
            $c->assertFalse($m->addAttachment($c));
        }, [$this]);
        
        $message->addBeforeSend(function (Email $m, TestCase $c) {
            $c->assertEquals(2, count($m->getAttachments()));
        }, [$this]); 
        $message->send();
        $this->assertEquals([
            'command' => 'QUIT',
            'code' => 221,
            'message' => '221 gator4189.hostgator.com closing connection',
            'time' => date('Y-m-d H:i:s'),
        ], $message->getSMTPServer()->getLastLogEntry());
        $message->send();
    }
    /**
     * @test
     */
    public function testSend04() {
        $this->expectException(SMTPException::class);
        $this->expectExceptionMessage('No message recipients.');
        $message = new Email(new SMTPAccount($this->acc01));

        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->send();
    }
    /**
     * @test
     */
    public function testTemplate00() {
        $message = new Email(new SMTPAccount($this->acc01));
        $message->insertFromTemplate('html-00.html', [
            'NAME' => 'Ibrahim'
        ]);
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
                . '            '.SMTPServer::NL
                . '    Hello Mr. Ibrahim'.SMTPServer::NL
                . ''.SMTPServer::NL
                . '        </p>'.SMTPServer::NL
                . '    </body>'.SMTPServer::NL
                . '</html>'.SMTPServer::NL, $message->getDocument()->toHTML());
        
    }
    /**
     * @test
     */
    public function testTemplate01() {
        $message = new Email(new SMTPAccount($this->acc01));
        $message->insertFromTemplate('html-01.html', [
            'NAME' => 'Ibrahim',
            'color' => 'blue'
        ]);
        
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
                . '            '.SMTPServer::NL
                . '    Hello Mr. Ibrahim'.SMTPServer::NL
                . ''.SMTPServer::NL
                . '        </p>'.SMTPServer::NL
                . '        <p>'.SMTPServer::NL
                . '            '.SMTPServer::NL
                . '    It is a good day outside. The sky is blue.'.SMTPServer::NL
                . ''.SMTPServer::NL
                . '        </p>'.SMTPServer::NL
                . '    </body>'.SMTPServer::NL
                . '</html>'.SMTPServer::NL, $message.'');
        
    }
    /**
     * @test
     */
    public function testTemplate02() {
        $message = new Email(new SMTPAccount($this->acc01));
        $message->insertFromTemplate('php-00.php', [
            'name' => 'Ibrahim'
        ]);
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
                . '            '.SMTPServer::NL
                . '    Hello Mr. Ibrahim'.SMTPServer::NL
                . '        </p>'.SMTPServer::NL
                . '    </body>'.SMTPServer::NL
                . '</html>'.SMTPServer::NL, $message->getDocument()->toHTML());
        
    }
    /**
     * @test
     */
    public function testTemplate03() {
        $message = new Email(new SMTPAccount($this->acc01));
        $message->insertFromTemplate('php-01.php', [
            'name' => 'Ibrahim',
            'color' => 'blue'
        ]);
        
        $this->assertEquals('<!DOCTYPE html>'.SMTPServer::NL
                . '<html>'.SMTPServer::NL
                . '    <head>'.SMTPServer::NL
                . '        <title>'.SMTPServer::NL
                . '            Default'.SMTPServer::NL
                . '        </title>'.SMTPServer::NL
                . '        <meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">'.SMTPServer::NL
                . '    </head>'.SMTPServer::NL
                . '    <body itemscope itemtype="http://schema.org/WebPage">'.SMTPServer::NL
                . "        <p>".SMTPServer::NL
                . "            ".SMTPServer::NL
                . "    Hello Mr. Ibrahim".SMTPServer::NL
                . "        </p>".SMTPServer::NL
                . "        <p>".SMTPServer::NL
                . "            ".SMTPServer::NL
                . "    It is a good day outside. The sky is blue.".SMTPServer::NL
                . "".SMTPServer::NL
                . '        </p>'.SMTPServer::NL
                . '    </body>'.SMTPServer::NL
                . '</html>'.SMTPServer::NL, $message.'');
        
    }
    /**
     * @test
     */
    public function testStoreMode00() {
        $message = new Email();
        $this->assertEquals(SendMode::PROD, $message->getMode());
        $this->assertFalse($message->setMode('random str'));
        $this->assertEquals(SendMode::PROD, $message->getMode());
        $this->assertTrue($message->setMode(SendMode::TEST_STORE, [
            'store-path' => __DIR__
        ]));
        $this->assertEquals(SendMode::TEST_STORE, $message->getMode());
        $message->send();
        $this->assertTrue(File::isFileExist(__DIR__.DIRECTORY_SEPARATOR.$message->getSubject().DIRECTORY_SEPARATOR.date('Y-m-d H-i-s').'.html'));
    }
    /**
     * @test
     */
    public function testStoreMode01() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Store path is not set for mode SendMode::TEST_STORE.');
        $message = new Email();
        $this->assertEquals(SendMode::PROD, $message->getMode());
        $this->assertTrue($message->setMode(SendMode::TEST_STORE, [
            
        ]));
        $this->assertEquals(SendMode::TEST_STORE, $message->getMode());
        $message->send();
        $this->assertTrue(File::isFileExist(__DIR__.DIRECTORY_SEPARATOR.$message->getSubject().DIRECTORY_SEPARATOR.date('Y-m-d H-i-s').'.html'));
    }
    /**
     * @test
     */
    public function testStoreMode02() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Store path does not exist: \''.__DIR__.DIRECTORY_SEPARATOR.'inv_p\'');
        $message = new Email();
        $this->assertEquals(SendMode::PROD, $message->getMode());
        $this->assertTrue($message->setMode(SendMode::TEST_STORE, [
            'store-path' => __DIR__.DIRECTORY_SEPARATOR.'inv_p'
        ]));
        $this->assertEquals(SendMode::TEST_STORE, $message->getMode());
        $message->send();
    }
    /**
     * @test
     */
    public function testSendMode00() {
        $message = new Email(new SMTPAccount($this->acc01));
        $this->assertTrue($message->setMode(SendMode::TEST_SEND, [
            'send-addresses' => [
                'ibinshikh@outlook.com'
            ]
        ]));
        $this->assertEquals(SendMode::TEST_SEND, $message->getMode());
        $message->send();
        $this->assertEquals([
            'command' => 'QUIT',
            'code' => 221,
            'message' => '221 gator4189.hostgator.com closing connection',
            'time' => date('Y-m-d H:i:s'),
        ], $message->getSMTPServer()->getLastLogEntry());
        $this->assertTrue(true);
    }
    /**
     * @test
     */
    public function testSendMode01() {
        $message = new Email(new SMTPAccount($this->acc01));
        $this->assertTrue($message->setMode(SendMode::TEST_SEND, [
            'send-addresses' => 'ibinshikh@outlook.com'
        ]));
        $this->assertEquals(SendMode::TEST_SEND, $message->getMode());
        $message->send();
        $this->assertEquals([
            'command' => 'QUIT',
            'code' => 221,
            'message' => '221 gator4189.hostgator.com closing connection',
            'time' => date('Y-m-d H:i:s'),
        ], $message->getSMTPServer()->getLastLogEntry());
        $this->assertTrue(true);
    }
    /**
     * @test
     */
    public function testSendMode02() {
        $this->expectException(SMTPException::class);
        $this->expectExceptionMessage('Recipients are not set for mode SendMode::TEST_SEND.');
        $message = new Email(new SMTPAccount($this->acc01));
        $this->assertTrue($message->setMode(SendMode::TEST_SEND, [
            
        ]));
        $this->assertEquals(SendMode::TEST_SEND, $message->getMode());
        $message->send();
    }
}













