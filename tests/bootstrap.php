<?php

/**
 * PHPUnit Bootstrap for Nominet EPP Tests
 * 
 * This file sets up the test environment by loading mock classes
 * and the main Nominet adapter.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define test constants
define('NOMINET_TEST_MODE', true);
define('NOMINET_TEST_HOST', 'testbed-epp.nominet.uk');
define('NOMINET_TEST_PORT', 700);

// Load mock classes (order matters due to dependencies)
require_once __DIR__ . '/Mocks/Registrar_Exception.php';
require_once __DIR__ . '/Mocks/Registrar_AdapterAbstract.php';
require_once __DIR__ . '/Mocks/Registrar_Domain.php';

// Load the main adapter
require_once dirname(__DIR__) . '/Nominet.php';

/**
 * Helper function to create a test domain
 *
 * @param string $name Domain name
 * @return Registrar_Domain
 */
function createTestDomain(string $name): Registrar_Domain
{
    $domain = new Registrar_Domain();
    $domain->setName($name);
    
    // Split into SLD and TLD
    $parts = explode('.', $name, 2);
    if (count($parts) === 2) {
        $domain->setSld($parts[0]);
        $domain->setTld($parts[1]);
    }
    
    return $domain;
}

/**
 * Helper function to create a domain with nameservers
 *
 * @param string $name Domain name
 * @param array $nameservers Array of nameserver hostnames
 * @return Registrar_Domain
 */
function createTestDomainWithNs(string $name, array $nameservers): Registrar_Domain
{
    $domain = createTestDomain($name);
    
    if (isset($nameservers[0])) $domain->setNs1($nameservers[0]);
    if (isset($nameservers[1])) $domain->setNs2($nameservers[1]);
    if (isset($nameservers[2])) $domain->setNs3($nameservers[2]);
    if (isset($nameservers[3])) $domain->setNs4($nameservers[3]);
    
    return $domain;
}

/**
 * Helper function to create a domain with contact
 *
 * @param string $name Domain name
 * @return Registrar_Domain
 */
function createTestDomainWithContact(string $name): Registrar_Domain
{
    $domain = createTestDomain($name);
    
    $contact = new Registrar_Domain_Contact();
    $contact->setFirstName('Test')
            ->setLastName('User')
            ->setEmail('test@example.com')
            ->setCompany('Test Company')
            ->setAddress1('123 Test Street')
            ->setCity('London')
            ->setZip('SW1A 1AA')
            ->setCountry('GB')
            ->setTel('+44.1234567890');
    
    $domain->setContactRegistrant($contact);
    
    return $domain;
}

/**
 * Helper function to generate a unique test domain name
 *
 * @param string $tld Top-level domain (default: co.uk)
 * @return string
 */
function generateUniqueTestDomain(string $tld = 'co.uk'): string
{
    return 'test-' . time() . '-' . mt_rand(1000, 9999) . '.' . $tld;
}

/**
 * Helper function to create adapter with test configuration
 *
 * @param bool $testMode Use test mode (default: true)
 * @return Registrar_Adapter_Nominet
 */
function createTestAdapter(bool $testMode = true): Registrar_Adapter_Nominet
{
    $options = [
        'username' => getenv('NOMINET_IPS_TAG') ?: 'TESTBED',
        'password' => getenv('NOMINET_PASSWORD') ?: 'testpassword',
        'test_mode' => $testMode,
    ];
    
    return new Registrar_Adapter_Nominet($options);
}
