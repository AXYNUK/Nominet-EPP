# Contributing to Nominet EPP

Thank you for your interest in contributing to the Nominet EPP registrar adapter for FOSSBilling!

## How to Contribute

### Reporting Bugs

If you find a bug, please open an issue with:

1. **Description**: Clear description of the bug
2. **Steps to Reproduce**: Exact steps to reproduce the issue
3. **Expected Behavior**: What should happen
4. **Actual Behavior**: What actually happens
5. **Environment**:
   - FOSSBilling version
   - PHP version
   - Operating system
   - Extension version
6. **Logs**: Relevant log entries (sanitize credentials!)

### Suggesting Features

Feature requests are welcome! Please include:

1. **Use Case**: Why is this feature needed?
2. **Description**: What should it do?
3. **Implementation Ideas**: How might it work?
4. **Compatibility**: Impact on existing functionality

### Pull Requests

We welcome pull requests! Please:

1. **Fork** the repository
2. **Create a branch**: `git checkout -b feature/your-feature-name`
3. **Make changes** following our coding standards
4. **Test thoroughly** (see Testing section)
5. **Commit** with clear messages
6. **Push** to your fork
7. **Submit PR** with description

## Development Setup

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/Nominet-EPP.git
cd Nominet-EPP

# Create development branch
git checkout -b feature/my-new-feature
```

## Coding Standards

### PHP Code Style

- Follow PSR-12 coding standards
- Use 4 spaces for indentation (not tabs)
- Keep lines under 120 characters
- Add PHPDoc comments for all methods
- Use meaningful variable names

Example:

```php
/**
 * Check if domain is available for registration
 *
 * @param Registrar_Domain $domain Domain object
 * @return bool True if available, false otherwise
 * @throws Registrar_Exception On EPP errors
 */
public function isDomainAvailable(Registrar_Domain $domain): bool
{
    $domainName = $domain->getName();
    $this->getLog()->info('Checking availability for: ' . $domainName);
    
    // Implementation...
}
```

### Error Handling

- Always throw `Registrar_Exception` for errors
- Include helpful error messages
- Log important events with `$this->getLog()`
- Never expose sensitive data in logs/errors

```php
// Good
throw new Registrar_Exception('Domain registration failed: Invalid nameservers');

// Bad
throw new Exception('Error');
```

### Logging

Use appropriate log levels:

```php
$this->getLog()->debug('EPP XML request: ' . $xml);
$this->getLog()->info('Domain registered successfully: ' . $domain);
$this->getLog()->warning('EPP connection slow: ' . $time . 'ms');
$this->getLog()->error('EPP login failed: ' . $message);
```

## Testing

### Required Tests

Before submitting PR, test:

1. **Domain availability check**
2. **Domain registration**
3. **Domain renewal**
4. **Domain transfer**
5. **Nameserver updates**
6. **EPP code retrieval**
7. **Error handling**

### Testing Environment

Use Nominet testbed:

```php
// config.php (local testing only)
return [
    'username' => 'TESTBED-TAG',
    'password' => 'testbed-password',
    'test_mode' => true,
];
```

### Manual Testing Checklist

- [ ] Available domain returns true
- [ ] Unavailable domain returns false
- [ ] Registration creates domain in testbed
- [ ] Renewal extends expiry date
- [ ] Transfer initiates transfer request
- [ ] NS update changes nameservers
- [ ] EPP code retrieval returns auth code
- [ ] Invalid credentials throw exception
- [ ] Connection errors handled gracefully
- [ ] All operations logged correctly

## Documentation

### Update Documentation

If your change affects usage:

1. Update README.md
2. Update INSTALL.md if installation changes
3. Add to CHANGELOG.md
4. Update code comments/PHPDoc

### Documentation Style

- Use clear, concise language
- Include code examples
- Add screenshots if helpful
- Keep formatting consistent

## Commit Messages

Use clear, descriptive commit messages:

```bash
# Good
git commit -m "Add support for .ltd.uk domain registration"
git commit -m "Fix EPP XML escaping for special characters"
git commit -m "Update README with testbed instructions"

# Bad
git commit -m "fix bug"
git commit -m "update"
git commit -m "changes"
```

### Commit Message Format

```
<type>: <subject>

<body>

<footer>
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Test additions/changes
- `chore`: Build/tooling changes

Example:

```
feat: Add support for DNSSEC management

Implement EPP commands for DNSSEC key management:
- secDNS:create for adding DS records
- secDNS:update for modifying keys
- secDNS:delete for removing DNSSEC

Closes #42
```

## Version Numbering

We follow Semantic Versioning (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backwards compatible)
- **PATCH**: Bug fixes

Current: 1.0.0

## Release Process

1. Update version in `manifest.json`
2. Update CHANGELOG.md
3. Create git tag: `git tag v1.1.0`
4. Push tag: `git push origin v1.1.0`
5. Create GitHub release
6. Attach Nominet.zip to release

## Code Review

All PRs require review before merging. Reviewers check:

- Code quality and standards
- Test coverage
- Documentation updates
- Backwards compatibility
- Security implications

## Security

### Reporting Security Issues

**Do not** open public issues for security vulnerabilities.

Email: security@axyn.co.uk

Include:
- Description of vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### Security Guidelines

- Never commit credentials
- Sanitize all user input
- Validate EPP responses
- Use prepared statements
- Escape XML properly
- Log security events

## Community Guidelines

- Be respectful and inclusive
- Provide constructive feedback
- Help others learn
- Follow code of conduct
- Credit contributors

## Questions?

- Open a discussion on GitHub
- Email: support@axyn.co.uk
- Check existing issues/PRs

## License

By contributing, you agree your contributions will be licensed under Apache License 2.0.

---

Thank you for contributing to Nominet EPP! 🎉
