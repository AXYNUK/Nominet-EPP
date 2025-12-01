# Nominet EPP Registrar - API Documentation

This document describes the API methods available in the Nominet EPP Registrar extension for FOSSBilling.

## Table of Contents

- [Overview](#overview)
- [Configuration](#configuration)
- [Domain Operations](#domain-operations)
- [Transfer Operations](#transfer-operations)
- [Error Handling](#error-handling)
- [EPP Protocol Details](#epp-protocol-details)

## Overview

The Nominet EPP Registrar adapter implements the `Registrar_AdapterAbstract` interface to provide domain registration services for UK domains through Nominet's EPP (Extensible Provisioning Protocol) server.

### Supported TLDs

- `.uk`
- `.co.uk`
- `.org.uk`
- `.me.uk`
- `.net.uk`
- `.ltd.uk`
- `.plc.uk`
- `.sch.uk`

### Requirements

- PHP 7.4 or higher
- OpenSSL extension
- Valid Nominet IPS TAG with EPP access
- FOSSBilling 0.5.0 or higher

## Configuration

### getConfig()

Returns the configuration form fields for the admin interface.

```php
public static function getConfig(): array
```

**Returns:** Array of configuration field definitions

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `username` | text | Yes | Your Nominet IPS TAG (e.g., `MYCOMPANY`) |
| `password` | password | Yes | EPP password for your TAG |
| `test_mode` | checkbox | No | Use Nominet OT&E testbed instead of production |

**Example Configuration:**

```php
[
    'username' => 'MYCOMPANYTAG',
    'password' => 'secure-epp-password',
    'test_mode' => false,
]
```

## Domain Operations

### isDomainAvailable()

Check if a domain is available for registration.

```php
public function isDomainAvailable(Registrar_Domain $domain): bool
```

**Parameters:**
- `$domain` - Domain object with SLD and TLD set

**Returns:** `true` if available, `false` if registered

**Example:**
```php
$domain = new Registrar_Domain();
$domain->setSld('example');
$domain->setTld('co.uk');

$available = $adapter->isDomainAvailable($domain);
```

### registerDomain()

Register a new domain.

```php
public function registerDomain(Registrar_Domain $domain): bool
```

**Parameters:**
- `$domain` - Domain object with full registration details

**Required Domain Properties:**
- `sld` - Second-level domain
- `tld` - Top-level domain
- `ns1`, `ns2` - At least 2 nameservers
- `registrationPeriod` - Years (2 minimum for .uk)
- `contactRegistrar` - Contact details array

**Contact Details Array:**
```php
[
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'phone' => '+44.2012345678',
    'address1' => '123 High Street',
    'city' => 'London',
    'state' => 'Greater London',
    'postcode' => 'SW1A 1AA',
    'country' => 'GB',
    'company' => 'Example Ltd', // Optional
]
```

**Returns:** `true` on success

**Throws:** `Registrar_Exception` on failure

### renewDomain()

Renew an existing domain.

```php
public function renewDomain(Registrar_Domain $domain): bool
```

**Parameters:**
- `$domain` - Domain to renew with `registrationPeriod` set

**Returns:** `true` on success

**Note:** Renewal period must be at least 2 years for .uk domains.

### modifyNs()

Update domain nameservers.

```php
public function modifyNs(Registrar_Domain $domain): bool
```

**Parameters:**
- `$domain` - Domain with new `ns1`, `ns2`, `ns3`, `ns4` values

**Returns:** `true` on success

**Example:**
```php
$domain->setNs1('ns1.newhost.co.uk');
$domain->setNs2('ns2.newhost.co.uk');
$adapter->modifyNs($domain);
```

### getDomainDetails()

Retrieve domain information.

```php
public function getDomainDetails(Registrar_Domain $domain): Registrar_Domain
```

**Parameters:**
- `$domain` - Domain to query

**Returns:** Updated domain object with current information

### getDomainContactDetails()

Get domain contact details.

```php
public function getDomainContactDetails(Registrar_Domain $domain): array
```

**Returns:** Array of contact information

## Transfer Operations

### transferDomain()

Request domain transfer (IPS TAG change).

```php
public function transferDomain(Registrar_Domain $domain): bool
```

**Parameters:**
- `$domain` - Domain to transfer

**Note:** Nominet transfers work via IPS TAG changes. The domain owner must authorize the transfer through Nominet's registrant portal.

**Returns:** `true` if transfer request submitted

### getEpp()

Get EPP authorization code.

```php
public function getEpp(Registrar_Domain $domain): string
```

**Note:** Nominet does not use traditional EPP authorization codes. Transfers are authorized by the registrant through Nominet's online portal. This method returns information about the transfer process.

## Error Handling

All operations throw `Registrar_Exception` on failure:

```php
try {
    $adapter->registerDomain($domain);
} catch (Registrar_Exception $e) {
    echo "Registration failed: " . $e->getMessage();
    // Error code from Nominet (if available)
    echo "Error code: " . $e->getCode();
}
```

### Common Error Codes

| Code | Meaning |
|------|---------|
| 1000 | Success |
| 2001 | Command syntax error |
| 2003 | Required parameter missing |
| 2005 | Invalid parameter value |
| 2201 | Authorization error |
| 2302 | Object exists (domain already registered) |
| 2303 | Object does not exist |
| 2304 | Object status prohibits operation |

## EPP Protocol Details

### Connection Details

| Environment | Hostname | Port |
|-------------|----------|------|
| Production | `epp.nominet.org.uk` | 700 |
| Testbed (OT&E) | `ote.nominet.org.uk` | 700 |

### EPP Extensions Used

This adapter implements the Nominet-specific EPP extensions:

- `urn:ietf:params:xml:ns:domain-1.0` - Standard domain operations
- `urn:ietf:params:xml:ns:contact-1.0` - Contact management
- `http://www.nominet.org.uk/epp/xml/std-notifications-1.2` - Nominet notifications

### Rate Limits

Nominet enforces rate limits on EPP connections:
- Maximum 10 requests per second
- Maximum 1000 requests per minute

The adapter handles these limits automatically with appropriate delays.

## Unsupported Operations

Due to Nominet's policies, the following operations are not available:

### deleteDomain()

```php
public function deleteDomain(Registrar_Domain $domain): never
```

**Throws:** `Registrar_Exception`

Nominet does not support programmatic domain deletion. Domain cancellation must be requested through the Nominet registrar portal.

### enablePrivacyProtection() / disablePrivacyProtection()

```php
public function enablePrivacyProtection(Registrar_Domain $domain): never
public function disablePrivacyProtection(Registrar_Domain $domain): never
```

**Throws:** `Registrar_Exception`

WHOIS privacy is not available for .uk domains. Nominet provides opt-out options through their registrant portal.

### modifyContact()

```php
public function modifyContact(Registrar_Domain $domain): never
```

**Throws:** `Registrar_Exception`

Contact modifications should be performed through the Nominet registrar portal to ensure proper verification.

## Logging

The adapter logs all EPP transactions through FOSSBilling's logging system:

```php
$log = $adapter->getLog();
```

Logs include:
- Connection events
- EPP request/response pairs (passwords redacted)
- Error details
- Transaction timing

## Thread Safety

Each adapter instance maintains its own EPP connection. For concurrent operations, use separate adapter instances.
