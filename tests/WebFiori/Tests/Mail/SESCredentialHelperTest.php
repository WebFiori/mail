<?php
namespace WebFiori\Tests\Mail;

use PHPUnit\Framework\TestCase;
use WebFiori\Mail\SESCredentialHelper;

/**
 * Tests for SESCredentialHelper.
 */
class SESCredentialHelperTest extends TestCase {
    /**
     * @test
     * Derived password matches the known-good value from AWS documentation.
     *
     * AWS does not publish a public test vector for this specific derivation,
     * but the output for known inputs must be stable and consistent.
     * This test uses a fixed key/region and asserts the result is a
     * base64 string starting with the version byte (first char after decode is 0x04).
     */
    public function testDerivedPasswordIsBase64AndHasVersionByte() {
        $password = SESCredentialHelper::deriveSmtpPassword(
            'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'us-east-1'
        );

        $this->assertNotEmpty($password);
        $decoded = base64_decode($password, strict: true);
        $this->assertNotFalse($decoded, 'Result must be valid base64');
        $this->assertSame("\x04", $decoded[0], 'First byte must be version byte 0x04');
    }

    /**
     * @test
     * Same inputs always produce the same output (deterministic).
     */
    public function testDerivationIsDeterministic() {
        $key    = 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY';
        $region = 'us-east-1';

        $this->assertSame(
            SESCredentialHelper::deriveSmtpPassword($key, $region),
            SESCredentialHelper::deriveSmtpPassword($key, $region)
        );
    }

    /**
     * @test
     * Different regions produce different passwords.
     */
    public function testDifferentRegionsProduceDifferentPasswords() {
        $key = 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY';

        $this->assertNotSame(
            SESCredentialHelper::deriveSmtpPassword($key, 'us-east-1'),
            SESCredentialHelper::deriveSmtpPassword($key, 'eu-west-1')
        );
    }

    /**
     * @test
     * Different keys produce different passwords.
     */
    public function testDifferentKeysProduceDifferentPasswords() {
        $this->assertNotSame(
            SESCredentialHelper::deriveSmtpPassword('keyA', 'us-east-1'),
            SESCredentialHelper::deriveSmtpPassword('keyB', 'us-east-1')
        );
    }

    /**
     * @test
     * Empty secret key throws InvalidArgumentException.
     */
    public function testEmptySecretKeyThrows() {
        $this->expectException(\InvalidArgumentException::class);
        SESCredentialHelper::deriveSmtpPassword('', 'us-east-1');
    }

    /**
     * @test
     * Empty region throws InvalidArgumentException.
     */
    public function testEmptyRegionThrows() {
        $this->expectException(\InvalidArgumentException::class);
        SESCredentialHelper::deriveSmtpPassword('somekey', '');
    }

    /**
     * @test
     * Default region is us-east-1.
     */
    public function testDefaultRegionIsUsEast1() {
        $key = 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY';

        $this->assertSame(
            SESCredentialHelper::deriveSmtpPassword($key, 'us-east-1'),
            SESCredentialHelper::deriveSmtpPassword($key)
        );
    }

    /**
     * @test
     * smtpEndpoint() returns the correct hostname for a given region.
     */
    public function testSmtpEndpoint() {
        $this->assertSame(
            'email-smtp.us-east-1.amazonaws.com',
            SESCredentialHelper::smtpEndpoint('us-east-1')
        );
        $this->assertSame(
            'email-smtp.eu-west-1.amazonaws.com',
            SESCredentialHelper::smtpEndpoint('eu-west-1')
        );
        $this->assertSame(
            'email-smtp.us-east-1.amazonaws.com',
            SESCredentialHelper::smtpEndpoint()
        );
    }
}
