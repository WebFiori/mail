<?php
namespace WebFiori\Tests\Mail;

use PHPUnit\Framework\TestCase;
use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\SMTPServer;

/**
 * Integration tests that require real SMTP credentials.
 * Run manually with: composer test-integration
 *
 * @group integration
 */
class IntegrationTest extends TestCase {
    private static $config = null;

    public static function setUpBeforeClass(): void {
        $configPath = __DIR__ . '/../../../config/accounts.php';
        $samplePath = __DIR__ . '/../../../config/accounts-sample.php';

        if (!file_exists($configPath)) {
            if (file_exists($samplePath)) {
                copy($samplePath, $configPath);
            }
        }

        if (file_exists($configPath)) {
            self::$config = require $configPath;
        }
    }

    private function skipIfDefault($accountConfig): void {
        foreach ($accountConfig as $value) {
            if (is_string($value) && (
                strpos($value, 'your-') === 0 ||
                $value === 'your-password' ||
                $value === 'your-app-password'
            )) {
                $this->markTestSkipped('Using default configuration values — real credentials required');
            }
        }
    }

    /**
     * @test
     */
    public function testConnectionToGmail(): void {
        $server = new SMTPServer('smtp.gmail.com', 465);
        $this->assertTrue($server->connect());
    }

    /**
     * @test
     */
    public function testConnectionToOutlook(): void {
        $server = new SMTPServer('smtp.outlook.com', 587);
        $this->assertTrue($server->connect());
    }

    /**
     * @test
     */
    public function testSendWithOtherSmtp(): void {
        if (!self::$config || !isset(self::$config['other-smtp-1'])) {
            $this->markTestSkipped('other-smtp-1 configuration not found');
        }
        $this->skipIfDefault(self::$config['other-smtp-1']);

        $message = new Email(new SMTPAccount(self::$config['other-smtp-1']));
        $message->setSubject('Integration Test - Other SMTP');
        $message->insert('p')->text('Integration test message.');
        $message->addTo(self::$config['recipients']['to'] ?? 'test@example.com');
        $message->send();

        $lastLog = $message->getSMTPServer()->getLastLogEntry();
        $this->assertEquals('QUIT', $lastLog['command']);
        $this->assertEquals(221, $lastLog['code']);
    }

    /**
     * @test
     */
    public function testSendWithGmail(): void {
        if (!self::$config || !isset(self::$config['gmail'])) {
            $this->markTestSkipped('Gmail configuration not found');
        }
        $this->skipIfDefault(self::$config['gmail']);

        $account = new SMTPAccount(self::$config['gmail']);
        $email = new Email($account);
        $email->setSubject('Integration Test - Gmail');
        $email->addTo(self::$config['recipients']['to'] ?? 'test@example.com');
        $email->insert('p')->text('Integration test via Gmail.');

        $this->assertTrue($email->send());
    }

    /**
     * @test
     */
    public function testSendWithOAuth(): void {
        if (!self::$config || !isset(self::$config['oauth']['microsoft'])) {
            $this->markTestSkipped('Microsoft OAuth configuration not found');
        }
        $this->skipIfDefault(self::$config['oauth']['microsoft']);

        $account = new SMTPAccount(self::$config['oauth']['microsoft']);
        $email = new Email($account);
        $email->setSubject('Integration Test - OAuth');
        $email->addTo(self::$config['recipients']['to'] ?? 'test@example.com');
        $email->insert('p')->text('Integration test via OAuth.');

        $this->assertTrue($email->send());
    }
}
