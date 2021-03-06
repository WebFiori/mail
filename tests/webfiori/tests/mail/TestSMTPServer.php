<?php

namespace webfiori\tests\mail;

use PHPUnit\Framework\TestCase;
use webfiori\email\SMTPServer;
/**
 * Description of TestSMTPServer
 *
 * @author Ibrahim
 */
class TestSMTPServer extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $server = new SMTPServer('smtp.gmail.com', 465);
        $this->assertEquals('smtp.gmail.com', $server->getHost());
        $this->assertEquals(465, $server->getPort());
        
        $this->assertTrue($server->connect());
    }
    /**
     * @test
     */
    public function test01() {
        $server = new SMTPServer('smtp.outlook.com', 587);
        $this->assertEquals('smtp.outlook.com', $server->getHost());
        $this->assertEquals(587, $server->getPort());
        
        $this->assertTrue($server->connect());
    }
}
