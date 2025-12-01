# Nominet EPP FOSSBilling Extension Constitution

## Core Principles

### I. FOSSBilling Compatibility
Every component must strictly adhere to FOSSBilling's Registrar Adapter interface. The extension extends `Registrar_AdapterAbstract` and implements all required methods. Compatibility with FOSSBilling 0.6.0+ is mandatory.

### II. EPP Protocol Compliance
The extension implements Nominet's EPP (Extensible Provisioning Protocol) following RFC 5730 (base protocol), RFC 5731 (domain mapping), and RFC 5733 (contact mapping). All XML commands must be properly escaped and validated.

### III. Security First (NON-NEGOTIABLE)
- All credentials must be handled securely and never exposed in logs or error messages
- SSL/TLS encryption is mandatory for all EPP connections (port 700)
- Input validation and XML escaping must prevent injection attacks
- Sensitive data must be sanitized before logging

### IV. Test Mode Support
The extension must support both production (epp.nominet.uk:700) and testbed (testbed-epp.nominet.uk:700) environments via a configurable toggle. This enables safe testing before production deployment.

### V. Error Handling & Logging
- All EPP errors must be caught and converted to `Registrar_Exception`
- Comprehensive logging at appropriate levels (debug, info, warning, error)
- User-friendly error messages that don't expose internal details
- EPP result codes must be properly interpreted and handled

### VI. UK Domain Specialization
The extension is specifically designed for Nominet's .uk namespace:
- Supported TLDs: .uk, .co.uk, .org.uk, .me.uk, .net.uk, .ltd.uk, .plc.uk, .sch.uk
- Must respect Nominet's policies (no privacy protection, no domain deletion)
- Contact management follows Nominet's requirements

## Technical Requirements

### PHP Requirements
- PHP 7.4+ compatibility required
- OpenSSL extension must be available and validated at runtime
- No external dependencies beyond FOSSBilling core

### Code Standards
- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Include PHPDoc comments for all public methods
- Keep methods focused and under 50 lines where possible

### EPP Implementation
- Binary framing protocol: 4-byte length prefix + XML payload
- Session management with proper login/logout handling
- Connection pooling/reuse where appropriate
- Timeout handling for network operations

## Quality Gates

### Required for Production
- [ ] All registrar interface methods implemented
- [ ] Test mode verified with Nominet testbed
- [ ] Error handling covers all EPP result codes
- [ ] Logging provides adequate debugging information
- [ ] Documentation complete (README, INSTALL, API)
- [ ] No credentials exposed in any log output

### Performance Standards
- EPP operations should complete within 30 seconds
- Connection establishment within 10 seconds
- Graceful handling of network timeouts

## Governance

This constitution governs all development on the Nominet EPP extension. Any changes to core EPP protocol handling or security measures require explicit documentation and review.

**Version**: 1.0.0 | **Ratified**: 2025-11-21 | **Last Amended**: 2025-11-21
