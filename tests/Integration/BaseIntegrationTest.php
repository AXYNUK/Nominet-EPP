<?php

use PHPUnit\Framework\TestCase;

/**
 * Base class for integration tests
 * 
 * Integration tests require a valid Nominet testbed account.
 * They make real EPP connections to ote.nominet.org.uk (testbed).
 * 
 * Environment variables required:
 *   NOMINET_TEST_USERNAME - Your testbed TAG-EPP username
 *   NOMINET_TEST_PASSWORD - Your testbed password
 * 
 * @group integration
 */
abstract class BaseIntegrationTest extends TestCase
{
    protected static ?Registrar_Adapter_Nominet $adapter = null;
    protected static bool $skipTests = false;
    protected static string $skipReason = '';

    public static function setUpBeforeClass(): void
    {
        $username = getenv('NOMINET_TEST_USERNAME');
        $password = getenv('NOMINET_TEST_PASSWORD');

        if (empty($username) || empty($password)) {
            self::$skipTests = true;
            self::$skipReason = 'Nominet testbed credentials not configured. ' .
                'Set NOMINET_TEST_USERNAME and NOMINET_TEST_PASSWORD environment variables.';
            return;
        }

        try {
            self::$adapter = new Registrar_Adapter_Nominet([
                'username' => $username,
                'password' => $password,
                'test_mode' => true, // Always use testbed for integration tests
            ]);
        } catch (Registrar_Exception $e) {
            self::$skipTests = true;
            self::$skipReason = 'Failed to create adapter: ' . $e->getMessage();
        }
    }

    protected function setUp(): void
    {
        if (self::$skipTests) {
            $this->markTestSkipped(self::$skipReason);
        }
    }

    /**
     * Get a unique domain name for testing
     * Uses timestamp to avoid conflicts
     */
    protected function getTestDomainName(string $tld = 'co.uk'): string
    {
        $prefix = 'test-' . date('Ymd-His') . '-' . rand(1000, 9999);
        return $prefix . '.' . $tld;
    }

    /**
     * Create a test domain object with standard test data
     */
    protected function createTestDomainObject(string $domainName): Registrar_Domain
    {
        $domain = new Registrar_Domain();
        
        $parts = explode('.', $domainName, 2);
        $domain->setSld($parts[0]);
        $domain->setTld($parts[1] ?? 'co.uk');
        
        $domain->setNs1('ns1.test.co.uk');
        $domain->setNs2('ns2.test.co.uk');
        $domain->setRegistrationPeriod(2);
        
        // Set contact details
        $domain->setContactRegistrar([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+44.1onal123456',
            'address1' => '123 Test Street',
            'city' => 'London',
            'state' => 'Greater London',
            'postcode' => 'SW1A 1AA',
            'country' => 'GB',
            'company' => 'Test Company Ltd',
        ]);
        
        return $domain;
    }

    /**
     * Get the adapter instance
     */
    protected function getAdapter(): Registrar_Adapter_Nominet
    {
        if (self::$adapter === null) {
            $this->fail('Adapter not initialized');
        }
        
        return self::$adapter;
    }

    /**
     * Sleep to respect rate limits
     */
    protected function respectRateLimit(): void
    {
        usleep(500000); // 500ms between requests
    }
}
