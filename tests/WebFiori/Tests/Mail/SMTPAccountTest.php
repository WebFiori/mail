<?php
namespace WebFiori\Tests\Mail;

use PHPUnit\Framework\TestCase;
use WebFiori\Mail\AccountOption;
use WebFiori\Mail\SMTPAccount;
/**
 * A test class for testing the class 'WebFiori\Mail\SMTPAccount'.
 *
 * @author Ibrahim
 */
class SMTPAccountTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $acc = new SMTPAccount();
        $this->assertSame(465,$acc->getPort());
        $this->assertEquals('',$acc->getAddress());
        $this->assertEquals('',$acc->getSenderName());
        $this->assertEquals('',$acc->getPassword());
        $this->assertEquals('',$acc->getServerAddress());
        $this->assertEquals('',$acc->getUsername());
    }
    /**
     * @test
     */
    public function test01() {
        $acc = new SMTPAccount([
            AccountOption::USERNAME => 'my-mail@example.com',
            AccountOption::PASSWORD => '123456',
            AccountOption::PORT => 25,
            AccountOption::SERVER_ADDRESS => 'mail.examplex.com',
            AccountOption::SENDER_NAME => 'Example Sender',
            AccountOption::SENDER_ADDRESS => 'no-reply@example.com',
            AccountOption::NAME => 'no-reply'
        ]);
        $this->assertSame(25,$acc->getPort());
        $this->assertEquals('no-reply@example.com',$acc->getAddress());
        $this->assertEquals('Example Sender',$acc->getSenderName());
        $this->assertEquals('123456',$acc->getPassword());
        $this->assertEquals('mail.examplex.com',$acc->getServerAddress());
        $this->assertEquals('my-mail@example.com',$acc->getUsername());
        $this->assertEquals('no-reply',$acc->getAccountName());
    }
    /**
     * @test
     */
    public function test02() {
        $acc = new SMTPAccount([
            AccountOption::USERNAME => 'my-mail@example.com',
            AccountOption::PASSWORD => '123456',
            AccountOption::PORT => 25,
            AccountOption::SERVER_ADDRESS => 'mail.examplex.com',
            AccountOption::SENDER_NAME => 'Example Sender',
            AccountOption::SENDER_ADDRESS => 'no-reply@example.com',
            AccountOption::NAME => 'no-reply'
        ]);
        $this->assertSame(25,$acc->getPort());
        $this->assertEquals('no-reply@example.com',$acc->getAddress());
        $this->assertEquals('Example Sender',$acc->getSenderName());
        $this->assertEquals('123456',$acc->getPassword());
        $this->assertEquals('mail.examplex.com',$acc->getServerAddress());
        $this->assertEquals('my-mail@example.com',$acc->getUsername());
        $this->assertEquals('no-reply',$acc->getAccountName());
    }
    /**
     * @test
     */
    public function testSetAddress() {
        $acc = new SMTPAccount();
        $acc->setAddress('ix@hhh.com');
        $this->assertEquals('ix@hhh.com',$acc->getAddress());
        $acc->setAddress('    hhgix@hhh.com    ');
        $this->assertEquals('hhgix@hhh.com',$acc->getAddress());
    }
    /**
     * @test
     */
    public function testSetPassword() {
        $acc = new SMTPAccount();
        $acc->setPassword(' 55664 $wwe ');
        $this->assertEquals(' 55664 $wwe ',$acc->getPassword());
    }
    /**
     * @test
     */
    public function testSetPort00() {
        $acc = new SMTPAccount();
        $acc->setPort('88');
        $this->assertEquals(88,$acc->getPort());
        $acc->setPort(0);
        $this->assertEquals(0,$acc->getPort());
        $acc->setPort(1);
        $this->assertEquals(1,$acc->getPort());
    }
    /**
     * @test
     */
    public function testSetServerAddress() {
        $acc = new SMTPAccount();
        $acc->setServerAddress('smtp.hhh.com');
        $this->assertEquals('smtp.hhh.com',$acc->getServerAddress());
        $acc->setAddress('    smtp.xhx.com    ');
        $this->assertEquals('smtp.xhx.com',$acc->getAddress());
    }
    /**
     * @test
     */
    public function testSetUsername() {
        $acc = new SMTPAccount();
        $acc->setUsername('webfiori@hello.com');
        $this->assertEquals('webfiori@hello.com',$acc->getUsername());
        $acc->setUsername('    webfiori@hello-00.com    ');
        $this->assertEquals('webfiori@hello-00.com',$acc->getUsername());
    }
    /**
     * @test
     */
    public function testAccessToken() {
        $acc = new SMTPAccount([
            AccountOption::ACCESS_TOKEN => 'test-token-123'
        ]);
        $this->assertEquals('test-token-123', $acc->getAccessToken());
        
        $acc->setAccessToken('new-token-456');
        $this->assertEquals('new-token-456', $acc->getAccessToken());
        
        $acc->setAccessToken(null);
        $this->assertNull($acc->getAccessToken());
    }
    /**
     * @test
     */
    public function testVerifySslDefaultsToTrue() {
        $acc = new SMTPAccount();
        $this->assertTrue($acc->isVerifySsl());
    }
    /**
     * @test
     */
    public function testAllowSelfSignedDefaultsToFalse() {
        $acc = new SMTPAccount();
        $this->assertFalse($acc->isAllowSelfSigned());
    }
    /**
     * @test
     */
    public function testVerifySslViaConstructor() {
        $acc = new SMTPAccount([
            AccountOption::VERIFY_SSL => false,
        ]);
        $this->assertFalse($acc->isVerifySsl());
    }
    /**
     * @test
     */
    public function testAllowSelfSignedViaConstructor() {
        $acc = new SMTPAccount([
            AccountOption::ALLOW_SELF_SIGNED => true,
        ]);
        $this->assertTrue($acc->isAllowSelfSigned());
    }
    /**
     * @test
     */
    public function testVerifySslSetter() {
        $acc = new SMTPAccount();
        $acc->setVerifySsl(false);
        $this->assertFalse($acc->isVerifySsl());
        $acc->setVerifySsl(true);
        $this->assertTrue($acc->isVerifySsl());
    }
    /**
     * @test
     */
    public function testAllowSelfSignedSetter() {
        $acc = new SMTPAccount();
        $acc->setAllowSelfSigned(true);
        $this->assertTrue($acc->isAllowSelfSigned());
        $acc->setAllowSelfSigned(false);
        $this->assertFalse($acc->isAllowSelfSigned());
    }
    /**
     * @test
     * Verify that verifySsl and allowSelfSigned can be independently configured:
     * verifySsl=true + allowSelfSigned=true is a valid combination for
     * internal servers with self-signed certificates.
     */
    public function testVerifySslTrueWithAllowSelfSignedTrue() {
        $acc = new SMTPAccount([
            AccountOption::VERIFY_SSL        => true,
            AccountOption::ALLOW_SELF_SIGNED => true,
        ]);
        $this->assertTrue($acc->isVerifySsl());
        $this->assertTrue($acc->isAllowSelfSigned());
    }
    /**
     * @test
     */
    public function testMaxRetriesDefaultsToThree() {
        $acc = new SMTPAccount();
        $this->assertSame(3, $acc->getMaxRetries());
    }
    /**
     * @test
     */
    public function testRetryBaseDelayDefaultsToOne() {
        $acc = new SMTPAccount();
        $this->assertSame(1, $acc->getRetryBaseDelay());
    }
    /**
     * @test
     */
    public function testMaxRetriesViaConstructor() {
        $acc = new SMTPAccount([AccountOption::MAX_RETRIES => 5]);
        $this->assertSame(5, $acc->getMaxRetries());
    }
    /**
     * @test
     */
    public function testRetryDelayViaConstructor() {
        $acc = new SMTPAccount([AccountOption::RETRY_DELAY => 2]);
        $this->assertSame(2, $acc->getRetryBaseDelay());
    }
    /**
     * @test
     */
    public function testMaxRetriesSetterGetter() {
        $acc = new SMTPAccount();
        $acc->setMaxRetries(0);
        $this->assertSame(0, $acc->getMaxRetries());
        $acc->setMaxRetries(10);
        $this->assertSame(10, $acc->getMaxRetries());
        // Negative values are ignored
        $acc->setMaxRetries(-1);
        $this->assertSame(10, $acc->getMaxRetries());
    }
    /**
     * @test
     */
    public function testRetryDelaySetterGetter() {
        $acc = new SMTPAccount();
        $acc->setRetryBaseDelay(3);
        $this->assertSame(3, $acc->getRetryBaseDelay());
        // Values less than 1 are ignored
        $acc->setRetryBaseDelay(0);
        $this->assertSame(3, $acc->getRetryBaseDelay());
    }
    /**
     * @test
     */
    public function testZeroRetriesDisablesRetry() {
        $acc = new SMTPAccount([AccountOption::MAX_RETRIES => 0]);
        $this->assertSame(0, $acc->getMaxRetries());
    }
}
