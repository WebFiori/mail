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
}
