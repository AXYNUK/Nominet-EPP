<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests for EPP XML command building
 * 
 * These tests verify that the adapter generates valid EPP XML
 * for various domain operations.
 */
class EppXmlBuilderTest extends TestCase
{
    private Registrar_Adapter_Nominet $adapter;

    protected function setUp(): void
    {
        $this->adapter = new Registrar_Adapter_Nominet([
            'username' => 'TESTIPS',
            'password' => 'testpassword',
            'test_mode' => true,
        ]);
    }

    /**
     * Use reflection to access private methods for testing
     */
    private function invokePrivateMethod(string $methodName, array $args = [])
    {
        $reflection = new ReflectionClass($this->adapter);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->adapter, $args);
    }

    /**
     * Test check XML generation
     */
    public function testBuildCheckXml(): void
    {
        $xml = $this->invokePrivateMethod('_buildEppXml', ['check', 'example.co.uk', []]);

        $this->assertStringContainsString('<?xml version="1.0"', $xml);
        $this->assertStringContainsString('<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">', $xml);
        $this->assertStringContainsString('<check>', $xml);
        $this->assertStringContainsString('domain:check', $xml);
        $this->assertStringContainsString('example.co.uk', $xml);
        $this->assertStringContainsString('<clTRID>', $xml);
        
        // Verify valid XML
        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xml), 'Generated XML should be valid');
    }

    /**
     * Test info XML generation
     */
    public function testBuildInfoXml(): void
    {
        $xml = $this->invokePrivateMethod('_buildEppXml', ['info', 'example.co.uk', []]);

        $this->assertStringContainsString('<info>', $xml);
        $this->assertStringContainsString('domain:info', $xml);
        $this->assertStringContainsString('example.co.uk', $xml);
        
        // Verify valid XML
        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xml), 'Generated XML should be valid');
    }

    /**
     * Test create XML generation with nameservers
     */
    public function testBuildCreateXml(): void
    {
        $params = [
            'nameservers' => ['ns1.example.com', 'ns2.example.com'],
            'period' => 2,
        ];

        $xml = $this->invokePrivateMethod('_buildEppXml', ['create', 'newdomain.co.uk', $params]);

        $this->assertStringContainsString('<create>', $xml);
        $this->assertStringContainsString('domain:create', $xml);
        $this->assertStringContainsString('newdomain.co.uk', $xml);
        $this->assertStringContainsString('ns1.example.com', $xml);
        $this->assertStringContainsString('ns2.example.com', $xml);
        $this->assertStringContainsString('domain:hostObj', $xml);
        $this->assertStringContainsString('unit="y"', $xml);
        $this->assertStringContainsString('>2<', $xml); // Period value
        
        // Verify valid XML
        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xml), 'Generated XML should be valid');
    }

    /**
     * Test create XML with default period
     */
    public function testBuildCreateXmlDefaultPeriod(): void
    {
        $params = [
            'nameservers' => ['ns1.example.com'],
        ];

        $xml = $this->invokePrivateMethod('_buildEppXml', ['create', 'newdomain.co.uk', $params]);

        // Default period should be 1
        $this->assertStringContainsString('>1<', $xml);
    }

    /**
     * Test renew XML generation
     */
    public function testBuildRenewXml(): void
    {
        $params = [
            'curExpDate' => '2024-12-31',
            'period' => 1,
        ];

        $xml = $this->invokePrivateMethod('_buildEppXml', ['renew', 'example.co.uk', $params]);

        $this->assertStringContainsString('<renew>', $xml);
        $this->assertStringContainsString('domain:renew', $xml);
        $this->assertStringContainsString('example.co.uk', $xml);
        $this->assertStringContainsString('curExpDate', $xml);
        $this->assertStringContainsString('2024-12-31', $xml);
        
        // Verify valid XML
        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xml), 'Generated XML should be valid');
    }

    /**
     * Test transfer XML generation
     */
    public function testBuildTransferXml(): void
    {
        $params = [
            'authCode' => 'ABC123XYZ',
        ];

        $xml = $this->invokePrivateMethod('_buildEppXml', ['transfer', 'example.co.uk', $params]);

        $this->assertStringContainsString('<transfer', $xml);
        $this->assertStringContainsString('op="request"', $xml);
        $this->assertStringContainsString('domain:transfer', $xml);
        $this->assertStringContainsString('example.co.uk', $xml);
        $this->assertStringContainsString('domain:authInfo', $xml);
        $this->assertStringContainsString('ABC123XYZ', $xml);
        
        // Verify valid XML
        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xml), 'Generated XML should be valid');
    }

    /**
     * Test update nameservers XML generation
     */
    public function testBuildUpdateNsXml(): void
    {
        $params = [
            'nameservers' => ['ns1.new.com', 'ns2.new.com', 'ns3.new.com'],
        ];

        $xml = $this->invokePrivateMethod('_buildEppXml', ['update_ns', 'example.co.uk', $params]);

        $this->assertStringContainsString('<update>', $xml);
        $this->assertStringContainsString('domain:update', $xml);
        $this->assertStringContainsString('example.co.uk', $xml);
        $this->assertStringContainsString('domain:add', $xml);
        $this->assertStringContainsString('domain:ns', $xml);
        $this->assertStringContainsString('ns1.new.com', $xml);
        $this->assertStringContainsString('ns2.new.com', $xml);
        $this->assertStringContainsString('ns3.new.com', $xml);
        
        // Verify valid XML
        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xml), 'Generated XML should be valid');
    }

    /**
     * Test XML escaping for special characters
     */
    public function testXmlEscapingSpecialCharacters(): void
    {
        $params = [
            'nameservers' => ['ns1.example.com'],
        ];

        // Domain with characters that need escaping
        $xml = $this->invokePrivateMethod('_buildEppXml', ['check', 'test&domain.co.uk', []]);

        // Should escape & to &amp;
        $this->assertStringContainsString('&amp;', $xml);
        
        // Verify valid XML
        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xml), 'Generated XML should handle special characters');
    }

    /**
     * Test transaction ID format
     */
    public function testTransactionIdFormat(): void
    {
        $xml = $this->invokePrivateMethod('_buildEppXml', ['check', 'example.co.uk', []]);

        // clTRID should be in format: command-timestamp
        preg_match('/<clTRID>([^<]+)<\/clTRID>/', $xml, $matches);
        $this->assertNotEmpty($matches[1]);
        $this->assertStringStartsWith('check-', $matches[1]);
    }

    /**
     * Test EPP namespace declarations
     */
    public function testEppNamespaceDeclarations(): void
    {
        $xml = $this->invokePrivateMethod('_buildEppXml', ['check', 'example.co.uk', []]);

        $this->assertStringContainsString('xmlns="urn:ietf:params:xml:ns:epp-1.0"', $xml);
        $this->assertStringContainsString('xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"', $xml);
    }

    /**
     * Test all supported TLDs in check command
     * 
     * @dataProvider supportedTldsProvider
     */
    public function testSupportedTlds(string $tld): void
    {
        $domain = 'testdomain.' . $tld;
        $xml = $this->invokePrivateMethod('_buildEppXml', ['check', $domain, []]);

        $this->assertStringContainsString($domain, $xml);
        
        // Verify valid XML
        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xml));
    }

    public function supportedTldsProvider(): array
    {
        return [
            'uk' => ['uk'],
            'co.uk' => ['co.uk'],
            'org.uk' => ['org.uk'],
            'me.uk' => ['me.uk'],
            'net.uk' => ['net.uk'],
            'ltd.uk' => ['ltd.uk'],
            'plc.uk' => ['plc.uk'],
            'sch.uk' => ['sch.uk'],
        ];
    }
}
