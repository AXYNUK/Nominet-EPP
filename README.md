# Nominet EPP Registrar Adapter for FOSSBilling

Official Nominet EPP integration for FOSSBilling, enabling automated domain registration and management for .uk domains.

## Features

- ✅ Domain availability checking
- ✅ Domain registration (.uk, .co.uk, .org.uk, .me.uk, .net.uk, .ltd.uk, .plc.uk, .sch.uk)
- ✅ Domain renewal
- ✅ Domain transfer
- ✅ Nameserver management
- ✅ EPP code retrieval
- ✅ Domain information queries
- ✅ Test mode support (Nominet testbed)

## Requirements

- FOSSBilling 0.6.0 or higher
- PHP 7.4 or higher
- OpenSSL PHP extension
- Nominet IPS Tag and EPP credentials

## Installation

### Method 1: Via FOSSBilling Admin Panel

1. Download the latest release ZIP from [Releases](https://github.com/AXYNUK/Nominet-EPP/releases)
2. Log in to your FOSSBilling admin panel
3. Navigate to **System** → **Extensions**
4. Click **Upload Extension**
5. Select the downloaded ZIP file
6. Activate the extension

### Method 2: Manual Installation

1. Download the latest release
2. Extract to `/var/www/fossbilling/src/library/Registrar/Adapter/`
3. Ensure the file is named `Nominet.php`
4. The extension will appear in the registrar list

## Configuration

1. Navigate to **System** → **Domain Registration** → **Registrars**
2. Find **Nominet EPP** and click **Configure**
3. Enter your credentials:
   - **Nominet Tag (IPS Tag)**: Your Nominet IPS Tag
   - **Password**: Your Nominet EPP password
   - **Test Mode**: Enable to use Nominet testbed environment

### Getting Nominet Credentials

1. Register as a Nominet member at [Nominet.uk](https://www.nominet.uk/)
2. Apply for an IPS Tag
3. Set up EPP access in your Nominet account
4. Generate EPP credentials

## Supported TLDs

- `.uk`
- `.co.uk`
- `.org.uk`
- `.me.uk`
- `.net.uk`
- `.ltd.uk`
- `.plc.uk`
- `.sch.uk`

## Testing

Before using in production:

1. Enable **Test Mode** in the configuration
2. Use Nominet's testbed environment credentials
3. Test domain registration, renewal, and transfer operations
4. Verify nameserver updates work correctly
5. Once verified, disable test mode and use production credentials

## Nominet EPP Endpoints

- **Production**: `epp.nominet.uk:700`
- **Testbed**: `testbed-epp.nominet.uk:700`

## Limitations

- **Privacy Protection**: Not available for .uk domains (no WHOIS privacy)
- **Domain Deletion**: Not supported (domains must expire naturally)
- **Contact Modification**: Must be done through Nominet's online services
- **Domain Locking**: Managed automatically by Nominet

## Troubleshooting

### Connection Issues

If you encounter SSL/TLS connection errors:

```bash
# Check OpenSSL is installed
php -m | grep openssl

# Verify SSL support in PHP
php -r "var_dump(extension_loaded('openssl'));"
```

### EPP Login Failed

- Verify your IPS Tag and password are correct
- Ensure you're using the correct environment (test/production)
- Check that your IP is whitelisted with Nominet

### Domain Registration Failed

- Verify the domain is available
- Ensure nameservers are valid
- Check your Nominet account has sufficient balance
- Verify your account permissions

## API Documentation

This adapter implements the FOSSBilling Registrar Interface:

- `isDomainAvailable()` - Check if domain can be registered
- `registerDomain()` - Register a new domain
- `transferDomain()` - Transfer domain to your account
- `renewDomain()` - Renew an existing domain
- `getDomainDetails()` - Get domain information
- `modifyNs()` - Update nameservers
- `getEpp()` - Retrieve EPP/transfer code

## Support

- **Issues**: [GitHub Issues](https://github.com/AXYNUK/Nominet-EPP/issues)
- **Email**: support@axyn.co.uk
- **Nominet Documentation**: [Nominet EPP Guide](https://registrars.nominet.uk/)

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

Apache License 2.0 - see [LICENSE](LICENSE) file for details

## Credits

Developed by [AXYN](https://axyn.co.uk) for the FOSSBilling community.

## Changelog

### Version 1.0.0 (2025-01-13)

- Initial release
- Complete Nominet EPP implementation
- Support for all .uk TLDs
- Test mode support
- Full documentation
