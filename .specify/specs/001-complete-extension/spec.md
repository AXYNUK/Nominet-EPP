# Feature Specification: Complete Nominet EPP Extension

## Overview

### Feature Name
Complete and Test Nominet EPP FOSSBilling Extension

### Feature ID
001-complete-extension

### Status
In Progress

### Priority
P1 - Critical

## Problem Statement

The Nominet EPP extension for FOSSBilling needs to be fully validated and tested to ensure production readiness. While the core EPP implementation exists, it requires:
1. Unit tests for all EPP operations
2. Integration testing with Nominet testbed
3. Enhanced error handling for edge cases
4. Performance optimization for connection handling
5. Proper packaging for FOSSBilling extension directory

## User Stories

### US-001: Domain Availability Check (P1)
**As a** hosting provider  
**I want to** check if a .uk domain is available  
**So that** I can inform customers before they attempt registration

**Acceptance Criteria:**
- [ ] Returns true for available domains
- [ ] Returns false for registered domains
- [ ] Handles invalid domain formats gracefully
- [ ] Works in both test and production modes
- [ ] Completes within 5 seconds

### US-002: Domain Registration (P1)
**As a** hosting provider  
**I want to** register a .uk domain for a customer  
**So that** they can use the domain for their services

**Acceptance Criteria:**
- [ ] Successfully registers available domains
- [ ] Associates correct nameservers
- [ ] Returns appropriate errors for unavailable domains
- [ ] Supports all 8 .uk TLD variants
- [ ] Creates proper contact records

### US-003: Domain Renewal (P1)
**As a** hosting provider  
**I want to** renew domains before they expire  
**So that** customers don't lose their domains

**Acceptance Criteria:**
- [ ] Successfully extends domain expiration
- [ ] Supports 1-10 year renewal periods
- [ ] Validates domain is eligible for renewal
- [ ] Returns new expiration date

### US-004: Domain Transfer (P1)
**As a** hosting provider  
**I want to** transfer domains to my management  
**So that** I can manage domains registered elsewhere

**Acceptance Criteria:**
- [ ] Initiates transfer with valid auth code
- [ ] Handles transfer rejections gracefully
- [ ] Provides transfer status updates
- [ ] Works with all supported TLDs

### US-005: Nameserver Management (P2)
**As a** hosting provider  
**I want to** update domain nameservers  
**So that** customers can point domains to new servers

**Acceptance Criteria:**
- [ ] Updates 1-4 nameservers
- [ ] Validates nameserver format
- [ ] Reflects changes in domain info
- [ ] Handles invalid nameservers appropriately

### US-006: EPP Code Retrieval (P2)
**As a** hosting provider  
**I want to** retrieve EPP/auth codes for domains  
**So that** customers can transfer domains away if needed

**Acceptance Criteria:**
- [ ] Returns valid EPP code
- [ ] Only works for domains under management
- [ ] Handles domains without EPP codes

### US-007: Test Mode Operation (P1)
**As a** developer  
**I want to** test all operations in Nominet testbed  
**So that** I can validate functionality before production

**Acceptance Criteria:**
- [ ] All operations work in testbed
- [ ] Easy toggle between test/production
- [ ] Clear indication of current mode
- [ ] Testbed credentials properly handled

## Non-Functional Requirements

### NFR-001: Performance
- EPP connection establishment: < 10 seconds
- Individual EPP operations: < 30 seconds
- Graceful timeout handling

### NFR-002: Security
- No credential exposure in logs
- SSL/TLS verification for all connections
- Input sanitization for all EPP commands

### NFR-003: Reliability
- Proper connection cleanup on errors
- Automatic retry for transient failures
- Clear error messages for debugging

### NFR-004: Maintainability
- PSR-12 code standards
- Comprehensive PHPDoc comments
- Modular EPP command builders

## Success Criteria

1. All unit tests pass (100% coverage of public methods)
2. Integration tests pass against Nominet testbed
3. Successfully registered test domains in testbed
4. Documentation complete and accurate
5. Extension packaged and ready for FOSSBilling directory

## Out of Scope

- Privacy protection (not supported by Nominet for .uk)
- Domain deletion (Nominet policy requires expiration)
- Contact modification via EPP (use Nominet portal)
- DNSSEC management (future enhancement)

## Dependencies

- FOSSBilling 0.6.0+ installed
- PHP 7.4+ with OpenSSL extension
- Nominet IPS Tag and EPP credentials
- Network access to port 700

## Timeline

- Phase 1: Unit test creation (2 days)
- Phase 2: Integration testing (2 days)
- Phase 3: Documentation finalization (1 day)
- Phase 4: Packaging and submission (1 day)

---

**Author**: AXYN  
**Created**: 2025-11-21  
**Last Updated**: 2025-11-21
