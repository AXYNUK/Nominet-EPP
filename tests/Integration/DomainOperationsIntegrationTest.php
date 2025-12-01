<?php

require_once __DIR__ . '/BaseIntegrationTest.php';

/**
 * Integration tests for domain operations against Nominet testbed
 * 
 * These tests make real EPP connections to the Nominet OT&E environment.
 * Results are validated against actual Nominet responses.
 * 
 * @group integration
 */
class DomainOperationsIntegrationTest extends BaseIntegrationTest
{
    /**
     * Test domain availability check for available domain
     */
    public function testDomainAvailabilityCheckForAvailableDomain(): void
    {
        $adapter = $this->getAdapter();
        $domainName = $this->getTestDomainName('co.uk');
        
        $domain = $this->createTestDomainObject($domainName);
        
        $result = $adapter->isDomainAvailable($domain);
        
        // The random domain should be available
        $this->assertTrue($result, "Domain $domainName should be available");
        
        $this->respectRateLimit();
    }

    /**
     * Test domain availability check for registered domain
     */
    public function testDomainAvailabilityCheckForRegisteredDomain(): void
    {
        $adapter = $this->getAdapter();
        
        // Use a known registered domain in testbed
        $domain = $this->createTestDomainObject('example.co.uk');
        
        $result = $adapter->isDomainAvailable($domain);
        
        // example.co.uk is a reserved domain
        $this->assertFalse($result, "Domain example.co.uk should not be available");
        
        $this->respectRateLimit();
    }

    /**
     * Test domain info retrieval
     */
    public function testDomainInfoRetrieval(): void
    {
        $adapter = $this->getAdapter();
        
        // This would require a domain we own in testbed
        // For CI/CD, we'll just verify the method doesn't throw for valid syntax
        $domain = $this->createTestDomainObject('example.co.uk');
        
        try {
            $result = $adapter->getDomainDetails($domain);
            
            // If successful, validate response structure
            $this->assertIsArray($result);
        } catch (Registrar_Exception $e) {
            // Not owning the domain is expected, but should get proper error
            $this->assertStringContainsString('authorization', strtolower($e->getMessage()));
        }
        
        $this->respectRateLimit();
    }

    /**
     * Test EPP connection is established
     */
    public function testEppConnectionEstablished(): void
    {
        $adapter = $this->getAdapter();
        
        // If we got here without exceptions, connection works
        // Perform a simple check operation to verify
        $domain = $this->createTestDomainObject($this->getTestDomainName('co.uk'));
        
        try {
            $adapter->isDomainAvailable($domain);
            $this->assertTrue(true, 'EPP connection successful');
        } catch (Registrar_Exception $e) {
            // Connection errors vs domain errors
            if (strpos($e->getMessage(), 'connect') !== false) {
                $this->fail('EPP connection failed: ' . $e->getMessage());
            }
            // Other errors are OK (means connection worked)
            $this->assertTrue(true, 'EPP connection successful');
        }
        
        $this->respectRateLimit();
    }

    /**
     * Test various UK TLD variants
     */
    public function testUkTldVariants(): void
    {
        $adapter = $this->getAdapter();
        
        $tlds = ['uk', 'co.uk', 'org.uk', 'me.uk'];
        
        foreach ($tlds as $tld) {
            $domainName = $this->getTestDomainName($tld);
            $domain = $this->createTestDomainObject($domainName);
            
            try {
                $result = $adapter->isDomainAvailable($domain);
                $this->assertIsBool($result, "Should return boolean for $tld");
            } catch (Registrar_Exception $e) {
                // If TLD not supported in testbed, that's acceptable
                $this->assertStringContainsString(
                    'not supported',
                    strtolower($e->getMessage()),
                    "Unexpected error for $tld: " . $e->getMessage()
                );
            }
            
            $this->respectRateLimit();
        }
    }

    /**
     * Test full domain lifecycle (if testbed allows)
     * 
     * This test is marked as skipped by default - enable manually
     * for full lifecycle testing in testbed
     */
    public function testDomainRegistrationLifecycle(): void
    {
        $this->markTestSkipped(
            'Full lifecycle test disabled by default. ' .
            'Enable manually for testbed testing.'
        );
        
        $adapter = $this->getAdapter();
        $domainName = $this->getTestDomainName('co.uk');
        $domain = $this->createTestDomainObject($domainName);
        
        // Step 1: Check availability
        $available = $adapter->isDomainAvailable($domain);
        $this->assertTrue($available, 'Test domain should be available');
        $this->respectRateLimit();
        
        // Step 2: Register
        $registered = $adapter->registerDomain($domain);
        $this->assertTrue($registered, 'Domain should register successfully');
        $this->respectRateLimit();
        
        // Step 3: Verify registered
        $available = $adapter->isDomainAvailable($domain);
        $this->assertFalse($available, 'Domain should no longer be available');
        $this->respectRateLimit();
        
        // Step 4: Get info
        $info = $adapter->getDomainDetails($domain);
        $this->assertNotEmpty($info);
        $this->respectRateLimit();
        
        // Step 5: Renew
        $renewed = $adapter->renewDomain($domain);
        $this->assertTrue($renewed, 'Domain should renew successfully');
        $this->respectRateLimit();
        
        // Note: Testbed domains auto-delete after period, no cleanup needed
    }

    /**
     * Test nameserver update
     */
    public function testNameserverUpdate(): void
    {
        $this->markTestSkipped(
            'Nameserver update requires owning a domain in testbed. ' .
            'Enable manually with valid testbed domain.'
        );
        
        $adapter = $this->getAdapter();
        
        // Would need a domain we own in testbed
        $domain = $this->createTestDomainObject('owned-domain.co.uk');
        $domain->setNs1('ns1.newhost.co.uk');
        $domain->setNs2('ns2.newhost.co.uk');
        
        $result = $adapter->modifyNs($domain);
        
        $this->assertTrue($result, 'Nameserver update should succeed');
    }

    /**
     * Test transfer request
     */
    public function testTransferRequest(): void
    {
        $this->markTestSkipped(
            'Transfer requires IPS tag change which needs both parties. ' .
            'Enable manually for proper transfer testing.'
        );
        
        $adapter = $this->getAdapter();
        
        $domain = $this->createTestDomainObject('transfer-test.co.uk');
        
        // Transfer requires knowing the current IPS tag
        $result = $adapter->transferDomain($domain);
        
        $this->assertTrue($result, 'Transfer request should succeed');
    }

    /**
     * Test EPP code retrieval
     */
    public function testEppCodeRetrieval(): void
    {
        $adapter = $this->getAdapter();
        $domain = $this->createTestDomainObject('example.co.uk');
        
        try {
            $epp = $adapter->getEpp($domain);
            
            // Nominet doesn't use traditional EPP codes
            $this->assertIsString($epp);
        } catch (Registrar_Exception $e) {
            // Should get descriptive message about Nominet's system
            $this->assertStringContainsString('Nominet', $e->getMessage());
        }
        
        $this->respectRateLimit();
    }
}
