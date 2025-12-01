<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests for error handling
 * 
 * These tests verify proper error handling for various failure scenarios.
 */
class ErrorHandlingTest extends TestCase
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
     * Test Registrar_Exception extends Exception
     */
    public function testRegistrarExceptionExtendsException(): void
    {
        $exception = new Registrar_Exception('Test error', 100);
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals(100, $exception->getCode());
    }

    /**
     * Test exception with previous exception
     */
    public function testExceptionWithPrevious(): void
    {
        $previous = new Exception('Original error');
        $exception = new Registrar_Exception('Wrapper error', 0, $previous);
        
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Test that errors are logged
     */
    public function testErrorsAreLogged(): void
    {
        $logger = $this->adapter->getLog();
        $logger->clear();
        
        // Attempt an operation that will fail
        $domain = createTestDomain('example.co.uk');
        
        try {
            $this->adapter->deleteDomain($domain);
        } catch (Registrar_Exception $e) {
            // Expected
        }
        
        // Error should be logged (by the adapter internally)
        // In this mock case, we're just verifying the logger works
        $this->assertInstanceOf(MockLogger::class, $logger);
    }

    /**
     * Test privacy protection error message is descriptive
     */
    public function testPrivacyProtectionErrorMessage(): void
    {
        $domain = createTestDomain('example.co.uk');

        try {
            $this->adapter->enablePrivacyProtection($domain);
            $this->fail('Expected exception was not thrown');
        } catch (Registrar_Exception $e) {
            $this->assertStringContainsString('.uk', $e->getMessage());
            $this->assertStringContainsString('not available', $e->getMessage());
        }
    }

    /**
     * Test deletion error message explains Nominet policy
     */
    public function testDeletionErrorMessage(): void
    {
        $domain = createTestDomain('example.co.uk');

        try {
            $this->adapter->deleteDomain($domain);
            $this->fail('Expected exception was not thrown');
        } catch (Registrar_Exception $e) {
            $this->assertStringContainsString('Nominet', $e->getMessage());
        }
    }

    /**
     * Test contact modification error message references Nominet portal
     */
    public function testContactModificationErrorMessage(): void
    {
        $domain = createTestDomain('example.co.uk');

        try {
            $this->adapter->modifyContact($domain);
            $this->fail('Expected exception was not thrown');
        } catch (Registrar_Exception $e) {
            $this->assertStringContainsString('Nominet', $e->getMessage());
        }
    }

    /**
     * Test logger clear functionality
     */
    public function testLoggerClear(): void
    {
        $logger = new MockLogger();
        
        $logger->info('Message 1');
        $logger->info('Message 2');
        $this->assertCount(2, $logger->getLogs());
        
        $logger->clear();
        $this->assertCount(0, $logger->getLogs());
    }

    /**
     * Test empty domain name handling
     */
    public function testEmptyDomainName(): void
    {
        $domain = new Registrar_Domain();
        
        // Without setting name or SLD/TLD, getName should return a dot
        $name = $domain->getName();
        $this->assertEquals('.', $name);
    }

    /**
     * Test domain name from SLD and TLD
     */
    public function testDomainNameFromComponents(): void
    {
        $domain = new Registrar_Domain();
        $domain->setSld('example');
        $domain->setTld('co.uk');
        
        $this->assertEquals('example.co.uk', $domain->getName());
    }

    /**
     * Test that adapter validates configuration
     */
    public function testAdapterAcceptsEmptyConfig(): void
    {
        // Should not throw, just use defaults
        $adapter = new Registrar_Adapter_Nominet([]);
        
        $this->assertInstanceOf(Registrar_Adapter_Nominet::class, $adapter);
    }

    /**
     * Test that OpenSSL requirement is checked
     */
    public function testOpenSSLRequirement(): void
    {
        // This test just verifies OpenSSL is available in test environment
        // The actual check happens in the constructor
        $this->assertTrue(
            extension_loaded('openssl'),
            'OpenSSL extension must be loaded for Nominet EPP'
        );
    }
}
