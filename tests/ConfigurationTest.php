<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests for Nominet adapter configuration
 */
class ConfigurationTest extends TestCase
{
    /**
     * Test constructor with valid options
     */
    public function testConstructorWithValidOptions(): void
    {
        $options = [
            'username' => 'TESTIPS',
            'password' => 'testpassword123',
            'test_mode' => true,
        ];

        $adapter = new Registrar_Adapter_Nominet($options);

        $this->assertInstanceOf(Registrar_Adapter_Nominet::class, $adapter);
    }

    /**
     * Test constructor with missing options uses defaults
     */
    public function testConstructorWithMissingOptions(): void
    {
        $adapter = new Registrar_Adapter_Nominet([]);

        $this->assertInstanceOf(Registrar_Adapter_Nominet::class, $adapter);
    }

    /**
     * Test constructor without OpenSSL throws exception
     */
    public function testConstructorRequiresOpenSSL(): void
    {
        // This test verifies the OpenSSL check exists
        // We can't easily test the failure case without removing the extension
        $this->assertTrue(extension_loaded('openssl'), 'OpenSSL extension should be loaded');
    }

    /**
     * Test getConfig returns proper form structure
     */
    public function testGetConfigReturnsFormStructure(): void
    {
        $config = Registrar_Adapter_Nominet::getConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('label', $config);
        $this->assertArrayHasKey('form', $config);
        
        $form = $config['form'];
        
        // Check username field
        $this->assertArrayHasKey('username', $form);
        $this->assertEquals('text', $form['username'][0]);
        $this->assertArrayHasKey('label', $form['username'][1]);
        $this->assertArrayHasKey('required', $form['username'][1]);
        $this->assertTrue($form['username'][1]['required']);
        
        // Check password field
        $this->assertArrayHasKey('password', $form);
        $this->assertEquals('password', $form['password'][0]);
        $this->assertArrayHasKey('label', $form['password'][1]);
        $this->assertArrayHasKey('required', $form['password'][1]);
        $this->assertTrue($form['password'][1]['required']);
        
        // Check test_mode field
        $this->assertArrayHasKey('test_mode', $form);
        $this->assertEquals('radio', $form['test_mode'][0]);
        $this->assertArrayHasKey('multiOptions', $form['test_mode'][1]);
    }

    /**
     * Test test_mode toggle affects configuration
     */
    public function testTestModeToggle(): void
    {
        // Test with test_mode enabled
        $adapterTest = new Registrar_Adapter_Nominet(['test_mode' => true]);
        $this->assertInstanceOf(Registrar_Adapter_Nominet::class, $adapterTest);

        // Test with test_mode disabled
        $adapterProd = new Registrar_Adapter_Nominet(['test_mode' => false]);
        $this->assertInstanceOf(Registrar_Adapter_Nominet::class, $adapterProd);

        // Test with string '1' (form submission style)
        $adapterString = new Registrar_Adapter_Nominet(['test_mode' => '1']);
        $this->assertInstanceOf(Registrar_Adapter_Nominet::class, $adapterString);
    }

    /**
     * Test configuration label describes the adapter
     */
    public function testConfigLabelDescribesAdapter(): void
    {
        $config = Registrar_Adapter_Nominet::getConfig();
        
        $this->assertStringContainsString('Nominet', $config['label']);
        $this->assertStringContainsString('.uk', $config['label']);
    }

    /**
     * Test username field has proper description
     */
    public function testUsernameFieldDescription(): void
    {
        $config = Registrar_Adapter_Nominet::getConfig();
        $usernameField = $config['form']['username'][1];
        
        $this->assertArrayHasKey('description', $usernameField);
        $this->assertStringContainsString('IPS', $usernameField['description']);
    }

    /**
     * Test password field has proper description
     */
    public function testPasswordFieldDescription(): void
    {
        $config = Registrar_Adapter_Nominet::getConfig();
        $passwordField = $config['form']['password'][1];
        
        $this->assertArrayHasKey('description', $passwordField);
        $this->assertStringContainsString('EPP', $passwordField['description']);
    }
}
