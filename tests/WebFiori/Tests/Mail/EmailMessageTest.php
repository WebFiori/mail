<?php
namespace WebFiori\Tests\Mail;

use PHPUnit\Framework\TestCase;
use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;
use WebFiori\Mail\Exceptions\SMTPException;
use WebFiori\Mail\SendMode;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\SMTPServer;
use WebFiori\File\Exceptions\FileException;
use WebFiori\File\File;

/**
 * Tests for Email class using FakeSMTPServer (no external dependencies).
 */
class EmailMessageTest extends TestCase {
    private static ?FakeSMTPServer $fakeServer = null;
    private static ?FakeSMTPServer $rejectServer = null;
    private static ?FakeSMTPServer $greylistServer = null;
    private static int $fakePort = 2525;
    private static int $rejectPort = 2526;
    private static int $greylistPort = 2527;

    public static function setUpBeforeClass(): void {
        self::$fakeServer = new FakeSMTPServer(self::$fakePort);
        self::$fakeServer->start();

        self::$rejectServer = new FakeSMTPServer(self::$rejectPort);
        self::$rejectServer->setRejectAuth(true);
        self::$rejectServer->start();

        self::$greylistServer = new FakeSMTPServer(self::$greylistPort);
        self::$greylistServer->setGreylist(true);
        self::$greylistServer->start();
    }

    public static function tearDownAfterClass(): void {
        if (self::$fakeServer) {
            self::$fakeServer->stop();
        }
        if (self::$rejectServer) {
            self::$rejectServer->stop();
        }
        if (self::$greylistServer) {
            self::$greylistServer->stop();
        }
    }

    private function getValidAccount(): array {
        return [
            AccountOption::PORT => self::$fakePort,
            AccountOption::SERVER_ADDRESS => '127.0.0.1',
            AccountOption::USERNAME => 'test@example.com',
            AccountOption::PASSWORD => 'password123',
            AccountOption::SENDER_NAME => 'Test Sender',
            AccountOption::SENDER_ADDRESS => 'test@example.com',
            AccountOption::NAME => 'test-account'
        ];
    }

    private function getRejectAccount(): array {
        return [
            AccountOption::PORT => self::$rejectPort,
            AccountOption::SERVER_ADDRESS => '127.0.0.1',
            AccountOption::USERNAME => 'test@example.com',
            AccountOption::PASSWORD => 'wrong-password',
            AccountOption::SENDER_NAME => 'Test Sender',
            AccountOption::SENDER_ADDRESS => 'test@example.com',
            AccountOption::NAME => 'reject-account'
        ];
    }

    /**
     * @test
     */
    public function testAddReciver00() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $this->assertFalse($sm->addTo('', ''));
        $this->assertFalse($sm->addTo('', 'Hello'));
        $this->assertFalse($sm->addTo('', 'hello@web.com'));
    }

    /**
     * @test
     */
    public function testAddReciver01() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $this->assertTrue($sm->addTo('   hello@>hello.com ', '  <Hello'));
        $this->assertEquals('=?UTF-8?B?SGVsbG8=?= <hello@hello.com>', $sm->getToStr());
        $this->assertEquals('Hello', $sm->getTo()['hello@hello.com']);
        $this->assertTrue($sm->addTo('  hello@>hello.com  ', ' <Hello2 '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>', $sm->getToStr());
        $this->assertTrue($sm->addTo(' hello-9@>hello.com ', '  Hel>lo-9'));
        $this->assertTrue($sm->addTo('   hello@s.com '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>,=?UTF-8?B?SGVsbG8tOQ==?= <hello-9@hello.com>,=?UTF-8?B?aGVsbG9Acy5jb20=?= <hello@s.com>', $sm->getToStr());
    }

    /**
     * @test
     */
    public function testAddReciver02() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $this->assertTrue($sm->addCC(' hello@>hello.com   ', ' <Hello '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8=?= <hello@hello.com>', $sm->getCCStr());
        $this->assertEquals('Hello', $sm->getCC()['hello@hello.com']);
        $this->assertTrue($sm->addCC('  hello@>hello.com  ', ' <Hello2 '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>', $sm->getCCStr());
        $this->assertTrue($sm->addCC(' hello-9@>hello.com  ', ' Hel>lo-9 '));
        $this->assertTrue($sm->addCC(' hello-9@x.com  '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>,=?UTF-8?B?SGVsbG8tOQ==?= <hello-9@hello.com>,=?UTF-8?B?aGVsbG8tOUB4LmNvbQ==?= <hello-9@x.com>', $sm->getCCStr());
    }

    /**
     * @test
     */
    public function testAddReciver03() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $this->assertTrue($sm->addBCC(' hello@>hello.com   ', '   <Hello'));
        $this->assertEquals('=?UTF-8?B?SGVsbG8=?= <hello@hello.com>', $sm->getBCCStr());
        $this->assertEquals('Hello', $sm->getBCC()['hello@hello.com']);
        $this->assertTrue($sm->addBCC('hello@>hello.com  ', '  <Hello2  '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>', $sm->getBCCStr());
        $this->assertTrue($sm->addBCC('hello-9@>hello.com   ', '  Hel>lo-9'));
        $this->assertTrue($sm->addBCC('hello-9@>hello.com   '));
        $this->assertEquals('=?UTF-8?B?SGVsbG8y?= <hello@hello.com>,=?UTF-8?B?aGVsbG8tOUBoZWxsby5jb20=?= <hello-9@hello.com>', $sm->getBCCStr());
    }

    /**
     * @test
     */
    public function testAddReciver04() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $sm->addRecipients(['hello@hello.com']);
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>', $sm->getToStr());
        $this->assertEquals('hello@hello.com', $sm->getTo()['hello@hello.com']);
        $sm->addRecipients(['hello2@hello.com' => 'Hello2']);
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>,=?UTF-8?B?SGVsbG8y?= <hello2@hello.com>', $sm->getToStr());
        $this->assertEquals('Hello2', $sm->getTo()['hello2@hello.com']);
    }

    /**
     * @test
     */
    public function testAddReciver05() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $sm->addRecipients(['hello@hello.com'], 'cc');
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>', $sm->getCCStr());
        $this->assertEquals('hello@hello.com', $sm->getCC()['hello@hello.com']);
        $sm->addRecipients(['hello2@hello.com' => 'Hello2'], 'cc');
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>,=?UTF-8?B?SGVsbG8y?= <hello2@hello.com>', $sm->getCCStr());
        $this->assertEquals('Hello2', $sm->getCC()['hello2@hello.com']);
    }

    /**
     * @test
     */
    public function testAddReciver06() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $sm->addRecipients(['hello@hello.com'], 'Bcc');
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>', $sm->getBCCStr());
        $this->assertEquals('hello@hello.com', $sm->getBCC()['hello@hello.com']);
        $sm->addRecipients(['hello2@hello.com' => 'Hello2'], 'BCc');
        $this->assertEquals('=?UTF-8?B?aGVsbG9AaGVsbG8uY29t?= <hello@hello.com>,=?UTF-8?B?SGVsbG8y?= <hello2@hello.com>', $sm->getBCCStr());
        $this->assertEquals('Hello2', $sm->getBCC()['hello2@hello.com']);
    }

    /**
     * @test
     */
    public function testBeforeSend00() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $sm->addBeforeSend(function (Email $e, string $name) {
            $e->insert('p')->setID('hello-parag')->text('Hello ' . $name);
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
            . "</html>\r\n", $sm . '');
    }

    /**
     * @test
     */
    public function testSetLang00() {
        $account = new SMTPAccount($this->getValidAccount());
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
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $sm->addAfterSend(function (Email $e, string $name) {
            $e->insert('p')->setID('hello-parag')->text('Hello ' . $name);
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
            . "</html>\r\n", $sm . '');
    }

    /**
     * @test
     */
    public function testConstructor00() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $this->assertEquals('', $sm->getSMTPServer()->getLastResponse());
        $this->assertSame(0, $sm->getSMTPServer()->getLastResponseCode());
        $this->assertSame(0, $sm->getPriority());
        $this->assertSame(5, $sm->getSMTPServer()->getTimeout());
    }

    /**
     * @test
     */
    public function testSetPriority00() {
        $account = new SMTPAccount($this->getValidAccount());
        $sm = new Email($account);
        $sm->setPriority(-2);
        $this->assertSame(-1, $sm->getPriority());
        $sm->setPriority(100);
        $this->assertSame(1, $sm->getPriority());
        $sm->setPriority(33);
        $this->assertEquals(1, $sm->getPriority());
        $sm->setPriority(-26544);
        $this->assertSame(-1, $sm->getPriority());
        $sm->setPriority(26544);
        $this->assertSame(1, $sm->getPriority());
        $sm->setPriority(0);
        $this->assertSame(0, $sm->getPriority());
    }

    /**
     * @test
     */
    public function testSend00() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $this->assertEquals([
            'command' => '',
            'code' => 0,
            'message' => '',
            'time' => ''
        ], $message->getSMTPServer()->getLastLogEntry());
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('recipient@example.com');
        $message->addBeforeSend(function (Email $m, TestCase $c) {
            $c->assertTrue($m->addAttachment(__DIR__ . DIRECTORY_SEPARATOR . 'Attach00.txt'));
            $c->assertFalse($m->addAttachment('NotExtst.txt'));
            $c->assertFalse($m->addAttachment(new File(__DIR__ . DIRECTORY_SEPARATOR . 'not-exist.txt')));
            $c->assertTrue($m->addAttachment(new File(__DIR__ . DIRECTORY_SEPARATOR . 'favicon.png')));
            $c->assertFalse($m->addAttachment($c));
        }, [$this]);
        $message->addBeforeSend(function (Email $m, TestCase $c) {
            $c->assertEquals(2, count($m->getAttachments()));
        }, [$this]);
        $message->send();
        $lastLog = $message->getSMTPServer()->getLastLogEntry();
        $this->assertEquals('QUIT', $lastLog['command']);
        $this->assertEquals(221, $lastLog['code']);
        $this->assertStringContainsString('closing connection', $lastLog['message']);
    }

    /**
     * @test
     */
    public function testSend01() {
        $this->expectException(SMTPException::class);
        $message = new Email(new SMTPAccount($this->getRejectAccount()));
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('recipient@example.com');
        $message->send();
    }

    /**
     * @test
     */
    public function testSend02() {
        $message = new Email(new SMTPAccount($this->getRejectAccount()));
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('recipient@example.com');

        try {
            $message->send();
            $this->fail('Expected SMTPException was not thrown');
        } catch (SMTPException $ex) {
            $this->assertStringContainsString('535', $ex->getMessage());
            $this->assertGreaterThan(0, count($ex->getLog()));
        }
    }

    /**
     * @test
     */
    public function testSend03() {
        $this->expectException(SMTPException::class);
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->addTo('recipient@example.com');
        $message->addBeforeSend(function (Email $m, TestCase $c) {
            $c->assertTrue($m->addAttachment(__DIR__ . DIRECTORY_SEPARATOR . 'Attach00.txt'));
            $c->assertFalse($m->addAttachment('NotExtst.txt'));
            $c->assertFalse($m->addAttachment(new File(__DIR__ . DIRECTORY_SEPARATOR . 'not-exist.txt')));
            $c->assertTrue($m->addAttachment(new File(__DIR__ . DIRECTORY_SEPARATOR . 'favicon.png')));
            $c->assertFalse($m->addAttachment($c));
        }, [$this]);
        $message->addBeforeSend(function (Email $m, TestCase $c) {
            $c->assertEquals(2, count($m->getAttachments()));
        }, [$this]);
        $message->send();
        $lastLog = $message->getSMTPServer()->getLastLogEntry();
        $this->assertEquals('QUIT', $lastLog['command']);
        $this->assertEquals(221, $lastLog['code']);
        // Sending again should throw
        $message->send();
    }

    /**
     * @test
     */
    public function testSend04() {
        $this->expectException(SMTPException::class);
        $this->expectExceptionMessage('No message recipients.');
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $message->setSubject('Test Email From WebFiori');
        $message->setPriority(1);
        $message->insert('p')->text('Super test message.');
        $message->send();
    }

    /**
     * @test
     */
    public function testTemplate00() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $message->insertFromTemplate('html-00.html', [
            'NAME' => 'Ibrahim'
        ]);
        $this->assertEquals('<!DOCTYPE html>' . SMTPServer::NL
            . '<html>' . SMTPServer::NL
            . '    <head>' . SMTPServer::NL
            . '        <title>' . SMTPServer::NL
            . '            Default' . SMTPServer::NL
            . '        </title>' . SMTPServer::NL
            . '        <meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . SMTPServer::NL
            . '    </head>' . SMTPServer::NL
            . '    <body itemscope itemtype="http://schema.org/WebPage">' . SMTPServer::NL
            . '        <p>' . SMTPServer::NL
            . '            ' . SMTPServer::NL
            . '    Hello Mr. Ibrahim' . SMTPServer::NL
            . '' . SMTPServer::NL
            . '        </p>' . SMTPServer::NL
            . '    </body>' . SMTPServer::NL
            . '</html>' . SMTPServer::NL, $message->getDocument()->toHTML());
    }

    /**
     * @test
     */
    public function testTemplate01() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $message->insertFromTemplate('html-01.html', [
            'NAME' => 'Ibrahim',
            'color' => 'blue'
        ]);

        $this->assertEquals('<!DOCTYPE html>' . SMTPServer::NL
            . '<html>' . SMTPServer::NL
            . '    <head>' . SMTPServer::NL
            . '        <title>' . SMTPServer::NL
            . '            Default' . SMTPServer::NL
            . '        </title>' . SMTPServer::NL
            . '        <meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . SMTPServer::NL
            . '    </head>' . SMTPServer::NL
            . '    <body itemscope itemtype="http://schema.org/WebPage">' . SMTPServer::NL
            . '        <p>' . SMTPServer::NL
            . '            ' . SMTPServer::NL
            . '    Hello Mr. Ibrahim' . SMTPServer::NL
            . '' . SMTPServer::NL
            . '        </p>' . SMTPServer::NL
            . '        <p>' . SMTPServer::NL
            . '            ' . SMTPServer::NL
            . '    It is a good day outside. The sky is blue.' . SMTPServer::NL
            . '' . SMTPServer::NL
            . '        </p>' . SMTPServer::NL
            . '    </body>' . SMTPServer::NL
            . '</html>' . SMTPServer::NL, $message . '');
    }

    /**
     * @test
     */
    public function testTemplate02() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $message->insertFromTemplate('php-00.php', [
            'name' => 'Ibrahim'
        ]);
        $this->assertEquals('<!DOCTYPE html>' . SMTPServer::NL
            . '<html>' . SMTPServer::NL
            . '    <head>' . SMTPServer::NL
            . '        <title>' . SMTPServer::NL
            . '            Default' . SMTPServer::NL
            . '        </title>' . SMTPServer::NL
            . '        <meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . SMTPServer::NL
            . '    </head>' . SMTPServer::NL
            . '    <body itemscope itemtype="http://schema.org/WebPage">' . SMTPServer::NL
            . '        <p>' . SMTPServer::NL
            . '            ' . SMTPServer::NL
            . '    Hello Mr. Ibrahim' . SMTPServer::NL
            . '        </p>' . SMTPServer::NL
            . '    </body>' . SMTPServer::NL
            . '</html>' . SMTPServer::NL, $message->getDocument()->toHTML());
    }

    /**
     * @test
     */
    public function testTemplate03() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $message->insertFromTemplate('php-01.php', [
            'name' => 'Ibrahim',
            'color' => 'blue'
        ]);

        $this->assertEquals('<!DOCTYPE html>' . SMTPServer::NL
            . '<html>' . SMTPServer::NL
            . '    <head>' . SMTPServer::NL
            . '        <title>' . SMTPServer::NL
            . '            Default' . SMTPServer::NL
            . '        </title>' . SMTPServer::NL
            . '        <meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . SMTPServer::NL
            . '    </head>' . SMTPServer::NL
            . '    <body itemscope itemtype="http://schema.org/WebPage">' . SMTPServer::NL
            . "        <p>" . SMTPServer::NL
            . "            " . SMTPServer::NL
            . "    Hello Mr. Ibrahim" . SMTPServer::NL
            . "        </p>" . SMTPServer::NL
            . "        <p>" . SMTPServer::NL
            . "            " . SMTPServer::NL
            . "    It is a good day outside. The sky is blue." . SMTPServer::NL
            . "" . SMTPServer::NL
            . '        </p>' . SMTPServer::NL
            . '    </body>' . SMTPServer::NL
            . '</html>' . SMTPServer::NL, $message . '');
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
        $this->assertTrue(File::isFileExist(__DIR__ . DIRECTORY_SEPARATOR . $message->getSubject() . DIRECTORY_SEPARATOR . date('Y-m-d H-i-s') . '.html'));
    }

    /**
     * @test
     */
    public function testStoreMode01() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Store path is not set for mode SendMode::TEST_STORE.');
        $message = new Email();
        $this->assertEquals(SendMode::PROD, $message->getMode());
        $this->assertTrue($message->setMode(SendMode::TEST_STORE, []));
        $this->assertEquals(SendMode::TEST_STORE, $message->getMode());
        $message->send();
    }

    /**
     * @test
     */
    public function testStoreMode02() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Store path does not exist: \'' . __DIR__ . DIRECTORY_SEPARATOR . 'inv_p\'');
        $message = new Email();
        $this->assertEquals(SendMode::PROD, $message->getMode());
        $this->assertTrue($message->setMode(SendMode::TEST_STORE, [
            'store-path' => __DIR__ . DIRECTORY_SEPARATOR . 'inv_p'
        ]));
        $this->assertEquals(SendMode::TEST_STORE, $message->getMode());
        $message->send();
    }

    /**
     * @test
     */
    public function testSendMode00() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $this->assertTrue($message->setMode(SendMode::TEST_SEND, [
            'send-addresses' => [
                'recipient@example.com'
            ]
        ]));
        $this->assertEquals(SendMode::TEST_SEND, $message->getMode());
        $message->send();
        $lastLog = $message->getSMTPServer()->getLastLogEntry();
        $this->assertEquals('QUIT', $lastLog['command']);
        $this->assertEquals(221, $lastLog['code']);
        $this->assertStringContainsString('closing connection', $lastLog['message']);
    }

    /**
     * @test
     */
    public function testSendMode01() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $this->assertTrue($message->setMode(SendMode::TEST_SEND, [
            'send-addresses' => 'recipient@example.com'
        ]));
        $this->assertEquals(SendMode::TEST_SEND, $message->getMode());
        $message->send();
        $lastLog = $message->getSMTPServer()->getLastLogEntry();
        $this->assertEquals('QUIT', $lastLog['command']);
        $this->assertEquals(221, $lastLog['code']);
        $this->assertStringContainsString('closing connection', $lastLog['message']);
    }

    /**
     * @test
     */
    public function testSendMode02() {
        $this->expectException(SMTPException::class);
        $this->expectExceptionMessage('Recipients are not set for mode SendMode::TEST_SEND.');
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $this->assertTrue($message->setMode(SendMode::TEST_SEND, []));
        $this->assertEquals(SendMode::TEST_SEND, $message->getMode());
        $message->send();
    }

    /**
     * @test
     */
    public function testFluentTo() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $result = $message->to('test@example.com', 'Test User');

        $this->assertInstanceOf(Email::class, $result);
        $this->assertSame($message, $result);
        $this->assertEquals('=?UTF-8?B?VGVzdCBVc2Vy?= <test@example.com>', $message->getToStr());
    }

    /**
     * @test
     */
    public function testFluentChaining() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));

        $result = $message->to('test@example.com')
            ->cc('cc@example.com')
            ->subject('Chained Test')
            ->priority(1);

        $this->assertInstanceOf(Email::class, $result);
        $this->assertSame($message, $result);
        $this->assertArrayHasKey('test@example.com', $message->getTo());
        $this->assertArrayHasKey('cc@example.com', $message->getCC());
        $this->assertEquals('Chained Test', $message->getSubject());
        $this->assertEquals(1, $message->getPriority());
    }

    /**
     * @test
     * Tests SMTPServer::reset() method.
     */
    public function testServerReset() {
        $server = new SMTPServer('127.0.0.1', self::$fakePort);
        $this->assertTrue($server->connect());
        $this->assertTrue($server->isConnected());
        $this->assertTrue($server->reset());
        $this->assertEquals(250, $server->getLastResponseCode());
    }

    /**
     * @test
     * Tests SMTPServer::reset() when not connected.
     */
    public function testServerResetNotConnected() {
        $server = new SMTPServer('127.0.0.1', self::$fakePort);
        $this->assertFalse($server->reset());
    }

    /**
     * @test
     * Tests Email::send() with an external SMTPServer instance.
     */
    public function testSendWithExternalServer() {
        $account = new SMTPAccount($this->getValidAccount());
        $server = new SMTPServer('127.0.0.1', self::$fakePort);
        $server->connect();
        $server->authLogin('test@example.com', 'password123');

        $message = new Email($account);
        $message->setSubject('External Server Test');
        $message->addTo('recipient@example.com');
        $message->insert('p')->text('Sent via external server.');

        $message->send($server);

        $lastLog = $message->getSMTPServer()->getLastLogEntry();
        $this->assertEquals('QUIT', $lastLog['command']);
        $this->assertEquals(221, $lastLog['code']);
    }

    /**
     * @test
     * Tests that multipart/alternative is included in sent message.
     */
    public function testMultipartAlternative() {
        $message = new Email(new SMTPAccount($this->getValidAccount()));
        $message->setSubject('Multipart Test');
        $message->addTo('recipient@example.com');
        $message->insert('p')->text('Hello plain text world.');
        $message->send();

        $log = $message->getSMTPServer()->getLog();
        $commands = array_column($log, 'command');

        $hasTextPlain = false;
        $hasTextHtml = false;
        $hasAlternative = false;

        foreach ($commands as $cmd) {
            if (str_contains($cmd, 'multipart/alternative')) {
                $hasAlternative = true;
            }
            if (str_contains($cmd, 'text/plain')) {
                $hasTextPlain = true;
            }
            if (str_contains($cmd, 'text/html')) {
                $hasTextHtml = true;
            }
        }

        $this->assertTrue($hasAlternative, 'multipart/alternative boundary should be present');
        $this->assertTrue($hasTextPlain, 'text/plain part should be present');
        $this->assertTrue($hasTextHtml, 'text/html part should be present');
    }

    /**
     * @test
     * Tests that greylisting (451) on RCPT TO triggers a retry and succeeds.
     */
    public function testGreylistingRetry() {
        $account = new SMTPAccount([
            AccountOption::PORT => self::$greylistPort,
            AccountOption::SERVER_ADDRESS => '127.0.0.1',
            AccountOption::USERNAME => 'test@example.com',
            AccountOption::PASSWORD => 'password123',
            AccountOption::SENDER_NAME => 'Test Sender',
            AccountOption::SENDER_ADDRESS => 'test@example.com',
            AccountOption::NAME => 'greylist-account'
        ]);

        $message = new Email($account);
        $message->setSubject('Greylist Test');
        $message->addTo('recipient@example.com');
        $message->insert('p')->text('This should succeed after retry.');
        $message->send();

        $lastLog = $message->getSMTPServer()->getLastLogEntry();
        $this->assertEquals('QUIT', $lastLog['command']);
        $this->assertEquals(221, $lastLog['code']);

        // Verify RSET was sent (indicates retry happened)
        $log = $message->getSMTPServer()->getLog();
        $commands = array_column($log, 'command');
        $this->assertTrue(in_array('RSET', $commands), 'RSET should have been sent for greylisting retry');
    }

    /**
     * @test
     * Tests sending via SmtpTransport directly.
     */
    public function testSendViaSmtpTransport() {
        $account = new SMTPAccount($this->getValidAccount());
        $transport = new \WebFiori\Mail\SmtpTransport($account);

        $message = new Email($account);
        $message->setSubject('Transport Test');
        $message->addTo('recipient@example.com');
        $message->insert('p')->text('Sent via SmtpTransport.');
        $message->send($transport);

        $this->assertTrue($message->isSent());
        $lastLog = $transport->getServer()->getLastLogEntry();
        $this->assertEquals('QUIT', $lastLog['command']);
        $this->assertEquals(221, $lastLog['code']);
    }

    /**
     * @test
     * Tests sending via a custom TransportInterface implementation.
     */
    public function testSendViaCustomTransport() {
        $nullTransport = new class implements \WebFiori\Mail\TransportInterface {
            public array $sent = [];
            public function send(\WebFiori\Mail\Email $message): void { $this->sent[] = $message; }
            public function getName(): string { return 'null'; }
        };

        $account = new SMTPAccount($this->getValidAccount());
        $message = new Email($account);
        $message->setSubject('Null Transport Test');
        $message->addTo('recipient@example.com');
        $message->insert('p')->text('Not actually sent.');
        $message->send($nullTransport);

        $this->assertTrue($message->isSent());
        $this->assertCount(1, $nullTransport->sent);
        $this->assertSame($message, $nullTransport->sent[0]);
    }
}
