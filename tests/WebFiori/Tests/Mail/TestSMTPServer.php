<?php
namespace WebFiori\Tests\Mail;

use PHPUnit\Framework\TestCase;
use WebFiori\Mail\SMTPServer;

/**
 * Tests for SMTPServer class using FakeSMTPServer.
 */
class TestSMTPServer extends TestCase {
    private static ?FakeSMTPServer $fakeServer = null;
    private static int $port = 2527;

    public static function setUpBeforeClass(): void {
        self::$fakeServer = new FakeSMTPServer(self::$port);
        self::$fakeServer->start();
    }

    public static function tearDownAfterClass(): void {
        if (self::$fakeServer) {
            self::$fakeServer->stop();
        }
    }

    /**
     * @test
     */
    public function test00() {
        $server = new SMTPServer('127.0.0.1', self::$port);
        $this->assertEquals('127.0.0.1', $server->getHost());
        $this->assertEquals(self::$port, $server->getPort());

        $this->assertTrue($server->connect());
    }

    /**
     * @test
     */
    public function test01() {
        $server = new SMTPServer('127.0.0.1', self::$port);
        $this->assertEquals('127.0.0.1', $server->getHost());
        $this->assertEquals(self::$port, $server->getPort());

        $this->assertTrue($server->connect());
        $this->assertTrue($server->isConnected());
    }
}
