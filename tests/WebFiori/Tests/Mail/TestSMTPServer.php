<?php
namespace WebFiori\Tests\Mail;

use PHPUnit\Framework\TestCase;
use WebFiori\Mail\Exceptions\SMTPException;
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

    /**
     * @test
     * Verify that SMTPServer defaults to 3 retries and 1s base delay.
     */
    public function testDefaultRetryValues() {
        $server = new SMTPServer('127.0.0.1', self::$port);
        $this->assertSame(3, (new \ReflectionProperty($server, 'maxRetries'))->getValue($server));
        $this->assertSame(1, (new \ReflectionProperty($server, 'retryBaseDelay'))->getValue($server));
    }

    /**
     * @test
     * A read timeout (stream_set_timeout fires) must throw SMTPException,
     * not block indefinitely.
     */
    public function testReadTimeoutThrowsException() {
        // Use a dedicated port for this test
        $slowPort = self::$port + 10;
        $slowServer = new FakeSMTPServer($slowPort);
        $slowServer->setGreetingDelay(5); // delay greeting by 5s
        $slowServer->start();

        // Set a 1s timeout — much shorter than the 5s greeting delay
        $client = new SMTPServer('127.0.0.1', $slowPort);
        $client->setTimeout(1); // 1 minute but we override via seconds below

        // Directly set responseTimeout to 1 second via reflection
        $prop = new \ReflectionProperty($client, 'responseTimeout');
        $prop->setValue($client, 1); // raw value used as minutes → 60s

        // We need actual seconds, so set to a fractional approach:
        // stream_set_timeout uses seconds, responseTimeout * 60 = timeout in seconds.
        // Set responseTimeout to a tiny value (0 minutes = 0 seconds won't work).
        // Instead use a dedicated short-timeout server: set to 1 second directly.
        // The fake server delays 5s; stream_set_timeout(conn, 1*60) = 60s — still too long.
        // So we set responseTimeout to the smallest useful value and use a longer delay.
        // Workaround: set responseTimeout via reflection to a very small fraction,
        // but since it must be int, test with greetingDelay > responseTimeout*60.
        // responseTimeout=1 → timeout=60s, greetingDelay must be >60s — impractical.
        // Instead, test by directly calling read() after stream_set_timeout(1s):
        $slowServer->stop();

        // Re-implement with a proper short-timeout fake connection
        $shortPort = self::$port + 11;
        $shortServer = new FakeSMTPServer($shortPort);
        $shortServer->setGreetingDelay(3);
        $shortServer->start();

        // Open raw stream and set 1-second read timeout, then test read() directly
        $conn = @stream_socket_client('tcp://127.0.0.1:'.$shortPort, $err, $errStr, 5);
        $this->assertIsResource($conn, 'Should connect to slow server');
        stream_set_timeout($conn, 1); // 1 second read timeout

        // Inject the connection into SMTPServer via reflection
        $client2 = new SMTPServer('127.0.0.1', $shortPort);
        $connProp = new \ReflectionProperty($client2, 'serverCon');
        $connProp->setValue($client2, $conn);
        $timeoutProp = new \ReflectionProperty($client2, 'responseTimeout');
        $timeoutProp->setValue($client2, 1); // triggers 60s in stream_set_timeout, but stream already has 1s

        $this->expectException(SMTPException::class);
        $this->expectExceptionMessageMatches('/timed out/');
        $client2->read(); // should timeout and throw

        fclose($conn);
        $shortServer->stop();
    }

    /**
     * @test
     * After N refused connections the server accepts — retry must succeed.
     */
    public function testRetrySucceedsAfterTransientFailure() {
        $retryPort = self::$port + 20;
        $retryServer = new FakeSMTPServer($retryPort);
        $retryServer->setRefuseCount(2); // refuse first 2, accept 3rd
        $retryServer->start();

        // 3 retries, 0s base delay (set via reflection to skip sleep in tests)
        $client = new SMTPServer('127.0.0.1', $retryPort, true, false, 3, 1);
        $delayProp = new \ReflectionProperty($client, 'retryBaseDelay');
        $delayProp->setValue($client, 0);

        // connect() should retry and eventually succeed on attempt 3
        $result = $client->connect();
        $this->assertTrue($result, 'connect() should succeed after retries');

        $retryServer->stop();
    }

    /**
     * @test
     * If all retry attempts are exhausted connect() must return false.
     */
    public function testRetryExhaustedReturnsFalse() {
        // Port with nothing listening — all connections will fail
        $deadPort = self::$port + 30;

        $client = new SMTPServer('127.0.0.1', $deadPort, true, false, 2, 1);
        // Zero out delays so the test doesn't take 3+ seconds
        $delayProp = new \ReflectionProperty($client, 'retryBaseDelay');
        $delayProp->setValue($client, 0);

        $result = $client->connect();
        $this->assertFalse($result, 'connect() should return false after all retries exhausted');
    }
}
