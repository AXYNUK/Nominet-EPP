# Testing Guide

This document describes how to run tests for the Nominet EPP Registrar extension.

## Table of Contents

- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Test Suites](#test-suites)
- [Running Unit Tests](#running-unit-tests)
- [Running Integration Tests](#running-integration-tests)
- [Test Coverage](#test-coverage)
- [Continuous Integration](#continuous-integration)
- [Writing Tests](#writing-tests)

## Requirements

- PHP 7.4+ with OpenSSL extension
- PHPUnit 9.x
- Composer (for dependency management)

## Quick Start

```bash
# Clone repository
git clone https://github.com/AXYNUK/Nominet-EPP.git
cd Nominet-EPP

# Install PHPUnit (if not using Composer)
composer global require phpunit/phpunit:^9.0

# Run unit tests
phpunit --testsuite Unit

# Run with coverage
phpunit --testsuite Unit --coverage-html coverage/
```

## Test Suites

### Unit Tests

Located in `tests/*.php` (excluding `Integration/` directory).

These tests:
- Run without network access
- Use mock objects for FOSSBilling dependencies
- Test individual methods in isolation
- Are fast and can run in CI

### Integration Tests

Located in `tests/Integration/*.php`.

These tests:
- Connect to Nominet's OT&E (testbed) server
- Require valid testbed credentials
- Test real EPP transactions
- Are slower and require network access

## Running Unit Tests

### Basic Run

```bash
phpunit --testsuite Unit
```

### With Coverage Report

```bash
phpunit --testsuite Unit --coverage-html coverage/
```

Then open `coverage/index.html` in a browser.

### Specific Test File

```bash
phpunit tests/ConfigurationTest.php
```

### Specific Test Method

```bash
phpunit --filter testGetConfigReturnsArray
```

### TestDox Output (Readable)

```bash
phpunit --testdox --testsuite Unit
```

Example output:
```
Configuration (Tests\Configuration)
 ✔ Get config returns array
 ✔ Get config contains username field
 ✔ Get config contains password field
 ✔ Constructor accepts config array
```

## Running Integration Tests

### Prerequisites

1. **Nominet OT&E Account**: Request testbed access from Nominet
2. **IPS TAG**: Must be enabled for EPP access in testbed
3. **Environment Variables**: Set your credentials

### Setting Up Credentials

```bash
# Linux/macOS
export NOMINET_TEST_USERNAME="YOURTAG"
export NOMINET_TEST_PASSWORD="your-testbed-password"

# Windows (PowerShell)
$env:NOMINET_TEST_USERNAME="YOURTAG"
$env:NOMINET_TEST_PASSWORD="your-testbed-password"
```

### Running Integration Tests

```bash
# Include integration tests
phpunit --testsuite Integration --group integration
```

### Running All Tests

```bash
# Edit phpunit.xml to remove integration exclusion, then:
phpunit
```

## Test Coverage

### Generating Coverage Reports

```bash
# HTML report
phpunit --coverage-html coverage/

# Text summary
phpunit --coverage-text

# Clover XML (for CI tools)
phpunit --coverage-clover coverage/clover.xml
```

### Current Coverage Targets

| Component | Target |
|-----------|--------|
| Configuration methods | 100% |
| XML builder methods | 90%+ |
| Domain operations | 85%+ |
| Error handling | 90%+ |

## Continuous Integration

### GitHub Actions

The project uses GitHub Actions for CI/CD. See `.github/workflows/tests.yml`.

Tests run automatically on:
- Push to `main`, `master`, or `develop`
- Pull requests to these branches

### CI Matrix

- PHP 7.4, 8.0, 8.1, 8.2, 8.3
- Ubuntu latest
- Integration tests only run on main branch with secrets

### Setting Up Secrets

In your GitHub repository settings, add:

| Secret | Description |
|--------|-------------|
| `NOMINET_TEST_USERNAME` | Your OT&E IPS TAG |
| `NOMINET_TEST_PASSWORD` | Your OT&E password |
| `CODECOV_TOKEN` | (Optional) For code coverage |

## Writing Tests

### Unit Test Structure

```php
<?php

use PHPUnit\Framework\TestCase;

class MyFeatureTest extends TestCase
{
    private Registrar_Adapter_Nominet $adapter;

    protected function setUp(): void
    {
        $this->adapter = new Registrar_Adapter_Nominet([
            'username' => 'TESTIPS',
            'password' => 'testpass',
            'test_mode' => true,
        ]);
    }

    public function testFeatureWorks(): void
    {
        // Arrange
        $domain = createTestDomain('example.co.uk');
        
        // Act
        $result = $this->adapter->someMethod($domain);
        
        // Assert
        $this->assertTrue($result);
    }
}
```

### Testing Private Methods

Use reflection to test private methods:

```php
public function testPrivateMethod(): void
{
    $method = new ReflectionMethod(
        Registrar_Adapter_Nominet::class,
        '_buildCheckXml'
    );
    $method->setAccessible(true);
    
    $result = $method->invoke($this->adapter, 'example.co.uk');
    
    $this->assertStringContainsString('<domain:check>', $result);
}
```

### Integration Test Structure

```php
<?php

require_once __DIR__ . '/BaseIntegrationTest.php';

/**
 * @group integration
 */
class MyIntegrationTest extends BaseIntegrationTest
{
    public function testRealEppConnection(): void
    {
        $adapter = $this->getAdapter();
        $domain = $this->createTestDomainObject($this->getTestDomainName());
        
        $result = $adapter->isDomainAvailable($domain);
        
        $this->assertIsBool($result);
        $this->respectRateLimit();
    }
}
```

### Helper Functions

The bootstrap provides helper functions:

```php
// Create a test domain object
$domain = createTestDomain('example.co.uk');

// Create adapter with test config
$adapter = new Registrar_Adapter_Nominet([
    'username' => 'TESTIPS',
    'password' => 'test',
    'test_mode' => true,
]);
```

### Mocking EPP Responses

For unit tests, mock the EPP responses:

```php
public function testWithMockedResponse(): void
{
    $mockXml = '<?xml version="1.0"?>
        <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
            <response>
                <result code="1000">
                    <msg>Command completed successfully</msg>
                </result>
            </response>
        </epp>';
    
    // Use reflection to call _parseResponse
    $method = new ReflectionMethod(
        Registrar_Adapter_Nominet::class,
        '_parseResponse'
    );
    $method->setAccessible(true);
    
    $result = $method->invoke($this->adapter, $mockXml);
    // ... assertions
}
```

## Troubleshooting

### Tests Can't Find Classes

Ensure bootstrap is configured in phpunit.xml:
```xml
<phpunit bootstrap="tests/bootstrap.php">
```

### Integration Tests Timeout

Nominet EPP can be slow. Increase timeout:
```php
$this->setTestTimeout(30); // 30 seconds
```

### Coverage Not Generated

Install Xdebug:
```bash
pecl install xdebug
```

Or use PCOV (faster):
```bash
pecl install pcov
```

### Mock Classes Not Found

Verify autoloading in bootstrap.php includes mock paths.
