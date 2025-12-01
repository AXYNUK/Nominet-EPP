<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests for domain operations
 * 
 * These tests verify the domain operation methods work correctly
 * without making actual EPP connections (using mocking).
 */
class DomainOperationsTest extends TestCase
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
     * Test domain name validation
     */
    public function testDomainNameFormat(): void
    {
        $domain = createTestDomain('example.co.uk');
        
        $this->assertEquals('example.co.uk', $domain->getName());
        $this->assertEquals('example', $domain->getSld());
        $this->assertEquals('co.uk', $domain->getTld());
    }

    /**
     * Test domain with nameservers
     */
    public function testDomainWithNameservers(): void
    {
        $domain = createTestDomainWithNs('example.co.uk', [
            'ns1.example.com',
            'ns2.example.com',
        ]);
        
        $this->assertEquals('ns1.example.com', $domain->getNs1());
        $this->assertEquals('ns2.example.com', $domain->getNs2());
        $this->assertNull($domain->getNs3());
        $this->assertNull($domain->getNs4());
    }

    /**
     * Test domain with contact information
     */
    public function testDomainWithContact(): void
    {
        $domain = createTestDomainWithContact('example.co.uk');
        
        $contact = $domain->getContactRegistrant();
        $this->assertNotNull($contact);
        $this->assertEquals('Test', $contact->getFirstName());
        $this->assertEquals('User', $contact->getLastName());
        $this->assertEquals('test@example.com', $contact->getEmail());
        $this->assertEquals('GB', $contact->getCountry());
    }

    /**
     * Test privacy protection throws exception (not supported)
     */
    public function testEnablePrivacyProtectionThrowsException(): void
    {
        $domain = createTestDomain('example.co.uk');

        $this->expectException(Registrar_Exception::class);
        $this->expectExceptionMessage('Privacy protection is not available');
        
        $this->adapter->enablePrivacyProtection($domain);
    }

    /**
     * Test disable privacy protection throws exception (not supported)
     */
    public function testDisablePrivacyProtectionThrowsException(): void
    {
        $domain = createTestDomain('example.co.uk');

        $this->expectException(Registrar_Exception::class);
        $this->expectExceptionMessage('Privacy protection is not available');
        
        $this->adapter->disablePrivacyProtection($domain);
    }

    /**
     * Test domain deletion throws exception (not supported)
     */
    public function testDeleteDomainThrowsException(): void
    {
        $domain = createTestDomain('example.co.uk');

        $this->expectException(Registrar_Exception::class);
        $this->expectExceptionMessage('not supported');
        
        $this->adapter->deleteDomain($domain);
    }

    /**
     * Test domain lock throws exception (managed by Nominet)
     */
    public function testLockDomainThrowsException(): void
    {
        $domain = createTestDomain('example.co.uk');

        $this->expectException(Registrar_Exception::class);
        
        $this->adapter->lock($domain);
    }

    /**
     * Test domain unlock throws exception (managed by Nominet)
     */
    public function testUnlockDomainThrowsException(): void
    {
        $domain = createTestDomain('example.co.uk');

        $this->expectException(Registrar_Exception::class);
        
        $this->adapter->unlock($domain);
    }

    /**
     * Test contact modification throws exception (use Nominet portal)
     */
    public function testModifyContactThrowsException(): void
    {
        $domain = createTestDomain('example.co.uk');

        $this->expectException(Registrar_Exception::class);
        $this->expectExceptionMessage('Nominet');
        
        $this->adapter->modifyContact($domain);
    }

    /**
     * Test unique domain name generation
     */
    public function testGenerateUniqueTestDomain(): void
    {
        $domain1 = generateUniqueTestDomain();
        $domain2 = generateUniqueTestDomain();
        
        $this->assertNotEquals($domain1, $domain2);
        $this->assertStringEndsWith('.co.uk', $domain1);
        $this->assertStringStartsWith('test-', $domain1);
    }

    /**
     * Test unique domain name with custom TLD
     */
    public function testGenerateUniqueTestDomainCustomTld(): void
    {
        $domain = generateUniqueTestDomain('org.uk');
        
        $this->assertStringEndsWith('.org.uk', $domain);
    }

    /**
     * Test expiration time handling
     */
    public function testExpirationTimeHandling(): void
    {
        $domain = createTestDomain('example.co.uk');
        $expTime = strtotime('2025-12-31');
        
        $domain->setExpirationTime($expTime);
        
        $this->assertEquals($expTime, $domain->getExpirationTime());
    }

    /**
     * Test registration time handling
     */
    public function testRegistrationTimeHandling(): void
    {
        $domain = createTestDomain('example.co.uk');
        $regTime = strtotime('2020-01-01');
        
        $domain->setRegistrationTime($regTime);
        
        $this->assertEquals($regTime, $domain->getRegistrationTime());
    }

    /**
     * Test EPP code handling
     */
    public function testEppCodeHandling(): void
    {
        $domain = createTestDomain('example.co.uk');
        $epp = 'ABC123XYZ789';
        
        $domain->setEpp($epp);
        
        $this->assertEquals($epp, $domain->getEpp());
    }

    /**
     * Test all four nameservers
     */
    public function testFourNameservers(): void
    {
        $domain = createTestDomainWithNs('example.co.uk', [
            'ns1.example.com',
            'ns2.example.com',
            'ns3.example.com',
            'ns4.example.com',
        ]);
        
        $this->assertEquals('ns1.example.com', $domain->getNs1());
        $this->assertEquals('ns2.example.com', $domain->getNs2());
        $this->assertEquals('ns3.example.com', $domain->getNs3());
        $this->assertEquals('ns4.example.com', $domain->getNs4());
    }

    /**
     * Test logger is available
     */
    public function testLoggerAvailable(): void
    {
        $logger = $this->adapter->getLog();
        
        $this->assertInstanceOf(MockLogger::class, $logger);
        
        // Test logging works
        $logger->debug('Test message');
        $logs = $logger->getLogs();
        
        $this->assertCount(1, $logs);
        $this->assertEquals('debug', $logs[0]['level']);
        $this->assertEquals('Test message', $logs[0]['message']);
    }

    /**
     * Test logger levels
     */
    public function testLoggerLevels(): void
    {
        $logger = new MockLogger();
        
        $logger->debug('Debug message');
        $logger->info('Info message');
        $logger->warning('Warning message');
        $logger->err('Error message');
        
        $this->assertCount(4, $logger->getLogs());
        $this->assertCount(1, $logger->getLogsByLevel('debug'));
        $this->assertCount(1, $logger->getLogsByLevel('info'));
        $this->assertCount(1, $logger->getLogsByLevel('warning'));
        $this->assertCount(1, $logger->getLogsByLevel('error'));
    }
}
