<?php
namespace WebFiori\Tests\Mail;

use PHPUnit\Framework\TestCase;
use WebFiori\Mail\MicrosoftOAuthProvider;
use WebFiori\Mail\OAuthTokenProvider;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;

/**
 * Tests for OAuthTokenProvider interface wiring and MicrosoftOAuthProvider.
 */
class OAuthTokenProviderTest extends TestCase {
    private static int $fakePort = 2542;
    private static ?FakeSMTPServer $server = null;

    public static function setUpBeforeClass(): void {
        self::$server = new FakeSMTPServer(self::$fakePort);
        self::$server->start();
    }

    public static function tearDownAfterClass(): void {
        if (self::$server !== null) {
            self::$server->stop();
            self::$server = null;
        }
    }
    /**
     * @test
     * MicrosoftOAuthProvider implements OAuthTokenProvider.
     */
    public function testMicrosoftProviderImplementsInterface() {
        $provider = new MicrosoftOAuthProvider('tenant', 'client', 'secret');
        $this->assertInstanceOf(OAuthTokenProvider::class, $provider);
    }

    /**
     * @test
     * Empty constructor arguments throw InvalidArgumentException.
     */
    public function testEmptyTenantIdThrows() {
        $this->expectException(\InvalidArgumentException::class);
        new MicrosoftOAuthProvider('', 'client', 'secret');
    }

    /**
     * @test
     */
    public function testEmptyClientIdThrows() {
        $this->expectException(\InvalidArgumentException::class);
        new MicrosoftOAuthProvider('tenant', '', 'secret');
    }

    /**
     * @test
     */
    public function testEmptyClientSecretThrows() {
        $this->expectException(\InvalidArgumentException::class);
        new MicrosoftOAuthProvider('tenant', 'client', '');
    }

    /**
     * @test
     * SMTPAccount defaults to no token provider.
     */
    public function testAccountHasNoTokenProviderByDefault() {
        $account = new SMTPAccount();
        $this->assertNull($account->getTokenProvider());
    }

    /**
     * @test
     * setTokenProvider() / getTokenProvider() round-trip.
     */
    public function testSetAndGetTokenProvider() {
        $account  = new SMTPAccount();
        $provider = new MicrosoftOAuthProvider('tenant', 'client', 'secret');
        $account->setTokenProvider($provider);
        $this->assertSame($provider, $account->getTokenProvider());
    }

    /**
     * @test
     * setTokenProvider(null) clears the provider.
     */
    public function testClearTokenProvider() {
        $account  = new SMTPAccount();
        $provider = new MicrosoftOAuthProvider('tenant', 'client', 'secret');
        $account->setTokenProvider($provider);
        $account->setTokenProvider(null);
        $this->assertNull($account->getTokenProvider());
    }

    /**
     * @test
     * SmtpTransport calls getToken() on the provider (not getAccessToken())
     * when a provider is set — verified via a spy provider.
     */
    public function testTransportCallsProviderGetToken() {
        // Spy provider that records calls and returns a fake token
        $spy = new class implements OAuthTokenProvider {
            public int $callCount = 0;
            public function getToken(): string {
                $this->callCount++;
                return 'spy-token-' . $this->callCount;
            }
        };

        $fakePort = self::$fakePort;
        $account  = new SMTPAccount([
            AccountOption::PORT           => $fakePort,
            AccountOption::SERVER_ADDRESS => '127.0.0.1',
            AccountOption::USERNAME       => 'test@example.com',
            AccountOption::SENDER_NAME    => 'Test',
            AccountOption::SENDER_ADDRESS => 'test@example.com',
            AccountOption::NAME           => 'test',
            // No password and no static token — only the spy provider
        ]);
        $account->setTokenProvider($spy);

        $email = new Email($account);
        $email->setSubject('OAuth Provider Test');
        $email->addTo('to@example.com');
        $email->insert('p')->text('Test.');

        // Provider token is fetched at send time (lazy), not before
        $this->assertSame(0, $spy->callCount, 'getToken() should not be called before send()');

        $email->send();

        $this->assertSame(1, $spy->callCount, 'getToken() should be called exactly once during send()');
    }

    /**
     * @test
     * Token provider takes precedence over static setAccessToken().
     */
    public function testProviderTakesPrecedenceOverStaticToken() {
        $spy = new class implements OAuthTokenProvider {
            public bool $called = false;
            public function getToken(): string {
                $this->called = true;
                return 'provider-token';
            }
        };

        $fakePort = self::$fakePort;
        $account  = new SMTPAccount([
            AccountOption::PORT           => $fakePort,
            AccountOption::SERVER_ADDRESS => '127.0.0.1',
            AccountOption::USERNAME       => 'test@example.com',
            AccountOption::SENDER_NAME    => 'Test',
            AccountOption::SENDER_ADDRESS => 'test@example.com',
            AccountOption::NAME           => 'test',
            AccountOption::ACCESS_TOKEN   => 'static-token', // also set static token
        ]);
        $account->setTokenProvider($spy);

        $email = new Email($account);
        $email->setSubject('Precedence Test');
        $email->addTo('to@example.com');
        $email->insert('p')->text('Test.');
        $email->send();

        $this->assertTrue($spy->called, 'Provider should be called even when static token is also set');
    }
}

