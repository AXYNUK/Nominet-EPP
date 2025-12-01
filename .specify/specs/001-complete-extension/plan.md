# Implementation Plan: Complete Nominet EPP Extension

## Technical Context

### Technology Stack
- **Language**: PHP 7.4+
- **Framework**: FOSSBilling 0.6+
- **Protocol**: EPP 1.0 (RFC 5730, 5731, 5733)
- **Transport**: SSL/TLS over TCP port 700
- **Testing**: PHPUnit 9.x

### Architecture Overview
```
FOSSBilling Admin Panel
        │
        ▼
Registrar_Adapter_Nominet (Nominet.php)
        │
        ├── Configuration (IPS Tag, Password, Test Mode)
        │
        ├── EPP Connection Handler
        │   ├── SSL/TLS Connection
        │   ├── Binary Framing (4-byte length prefix)
        │   └── Session Management (Login/Logout)
        │
        └── EPP Commands
            ├── check (availability)
            ├── info (domain details)
            ├── create (registration)
            ├── renew (extension)
            ├── transfer (initiate)
            └── update (nameservers)
```

### File Structure
```
Nominet-EPP/
├── Nominet.php              # Main adapter (existing)
├── manifest.json            # Extension metadata (existing)
├── tests/
│   ├── bootstrap.php        # Test setup
│   ├── NominetAdapterTest.php
│   ├── EppConnectionTest.php
│   └── EppCommandsTest.php
├── .specify/                # Spec Kit files
└── docs/
    ├── README.md
    ├── INSTALL.md
    └── API.md
```

## Constitution Compliance

| Principle | Compliance | Notes |
|-----------|------------|-------|
| I. FOSSBilling Compatibility | ✅ | Extends Registrar_AdapterAbstract |
| II. EPP Protocol Compliance | ✅ | RFC-compliant implementation |
| III. Security First | ✅ | No credential logging, SSL/TLS |
| IV. Test Mode Support | ✅ | Testbed toggle implemented |
| V. Error Handling | ⚠️ | Needs enhancement |
| VI. UK Domain Specialization | ✅ | All 8 TLDs supported |

## Implementation Phases

### Phase 1: Test Infrastructure (Priority: P1)

#### Task 1.1: Create PHPUnit Bootstrap
- Set up autoloading for FOSSBilling classes
- Create mock classes for Registrar_Domain, Registrar_Exception
- Configure test environment variables

#### Task 1.2: Unit Tests for Configuration
- Test constructor with valid/invalid options
- Test getConfig() returns correct form structure
- Verify test_mode toggle behavior

#### Task 1.3: Unit Tests for EPP XML Building
- Test _buildCheckXml() output format
- Test _buildInfoXml() output format
- Test _buildCreateXml() with various parameters
- Test _buildRenewXml() with period handling
- Test _buildTransferXml() with auth codes
- Test _buildUpdateNsXml() with nameserver lists

### Phase 2: EPP Connection Tests (Priority: P1)

#### Task 2.1: Connection Handler Tests
- Mock SSL socket connections
- Test binary framing (read/write)
- Test timeout handling
- Test connection error scenarios

#### Task 2.2: Session Management Tests
- Test login command generation
- Test logout command generation
- Test session state management
- Test reconnection on timeout

### Phase 3: Integration Testing (Priority: P1)

#### Task 3.1: Testbed Configuration
- Document testbed credential setup
- Create test configuration file
- Set up test domain names

#### Task 3.2: Live Testbed Tests
- Test domain availability check
- Test domain registration flow
- Test domain renewal
- Test nameserver update
- Test EPP code retrieval

### Phase 4: Error Handling Enhancement (Priority: P2)

#### Task 4.1: EPP Error Code Handling
- Map all EPP result codes to exceptions
- Add specific exception types
- Improve error messages

#### Task 4.2: Network Error Handling
- Handle connection timeouts
- Handle SSL certificate errors
- Handle malformed responses

### Phase 5: Documentation & Packaging (Priority: P2)

#### Task 5.1: Complete Documentation
- Update README with test instructions
- Add API documentation
- Create troubleshooting guide

#### Task 5.2: Extension Packaging
- Create release ZIP
- Verify manifest.json
- Test installation process

## Data Model

### Configuration Structure
```php
$config = [
    'username' => string,    // Nominet IPS Tag
    'password' => string,    // EPP password
    'test_mode' => bool,     // Use testbed environment
];
```

### EPP Response Structure
```xml
<?xml version="1.0" encoding="UTF-8"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <response>
    <result code="1000">
      <msg>Command completed successfully</msg>
    </result>
    <resData>
      <!-- Domain data -->
    </resData>
    <trID>
      <clTRID>check-123456789</clTRID>
      <svTRID>SV123456</svTRID>
    </trID>
  </response>
</epp>
```

## API Contracts

### isDomainAvailable(Registrar_Domain $domain): bool
- **Input**: Domain object with name set
- **Output**: true if available, false if registered
- **Errors**: Registrar_Exception on EPP errors

### registerDomain(Registrar_Domain $domain): bool
- **Input**: Domain with name, nameservers, contact
- **Output**: true on success
- **Errors**: Registrar_Exception on failure

### renewDomain(Registrar_Domain $domain): bool
- **Input**: Domain with expiration date
- **Output**: true on success
- **Errors**: Registrar_Exception on failure

### transferDomain(Registrar_Domain $domain): bool
- **Input**: Domain with EPP code
- **Output**: true on transfer initiated
- **Errors**: Registrar_Exception on failure

### modifyNs(Registrar_Domain $domain): bool
- **Input**: Domain with updated nameservers
- **Output**: true on success
- **Errors**: Registrar_Exception on failure

### getEpp(Registrar_Domain $domain): string
- **Input**: Domain object
- **Output**: EPP/auth code string
- **Errors**: Registrar_Exception if not found

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Testbed credentials unavailable | Medium | High | Use mock tests as fallback |
| EPP protocol changes | Low | Medium | Monitor Nominet announcements |
| Network instability | Medium | Low | Implement retries, timeouts |
| FOSSBilling API changes | Low | High | Pin to 0.6+ compatibility |

## Quick Start for Development

```bash
# Clone repository
git clone https://github.com/AXYNUK/Nominet-EPP.git
cd Nominet-EPP

# Install test dependencies
composer require --dev phpunit/phpunit

# Run tests
./vendor/bin/phpunit tests/

# Test with real testbed (requires credentials)
NOMINET_IPS_TAG=TESTBED NOMINET_PASSWORD=xxx ./vendor/bin/phpunit tests/ --group integration
```

---

**Plan Version**: 1.0.0  
**Created**: 2025-11-21  
**Last Updated**: 2025-11-21
