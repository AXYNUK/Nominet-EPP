# Nominet EPP - Quick Reference

## Installation (One Command)

```bash
ssh root@212.227.127.30 "cd /var/www/fossbilling/src/library/Registrar/Adapter && wget https://github.com/AXYNUK/Nominet-EPP/releases/download/v1.0.0/Nominet.zip && unzip Nominet.zip && rm Nominet.zip && chown nginx:nginx Nominet.php manifest.json"
```

## Configuration

1. Admin Panel: https://portal.axyn.co.uk/admin
2. System → Domain Registration → Registrars
3. Configure Nominet EPP:
   - **Nominet Tag**: Your IPS Tag
   - **Password**: Your EPP password
   - **Test Mode**: ☑ (initially)

## Quick Test

```bash
# Check if extension is installed
ssh root@212.227.127.30 "ls -la /var/www/fossbilling/src/library/Registrar/Adapter/Nominet.php"

# Expected: -rw-r--r-- 1 nginx nginx 18XXX Nov XX XX:XX Nominet.php
```

## Supported TLDs

- .uk
- .co.uk
- .org.uk
- .me.uk
- .net.uk
- .ltd.uk
- .plc.uk
- .sch.uk

## Common Tasks

### Enable TLDs
1. System → Domain Registration → Registrars
2. Click "TLDs" tab
3. Enable: .uk, .co.uk, .org.uk, etc.
4. Set registrar to "Nominet EPP"
5. Set pricing

### Test Domain Availability
1. Domain Registration → Check Domain
2. Enter: test123456.co.uk
3. Should return available/unavailable

### Switch to Production
1. System → Domain Registration → Registrars
2. Configure Nominet EPP
3. **Uncheck** Test Mode
4. Enter production credentials
5. Save

### View Logs
```bash
# Application logs
ssh root@212.227.127.30 "tail -f /var/www/fossbilling/data/log/application.log"

# Nginx error logs
ssh root@212.227.127.30 "tail -f /var/log/nginx/error.log"
```

## Troubleshooting

### Extension Not Showing
```bash
# Clear cache
ssh root@212.227.127.30 "rm -rf /var/www/fossbilling/data/cache/*"
```

### Connection Failed
```bash
# Test port 700 access
ssh root@212.227.127.30 "timeout 5 bash -c '</dev/tcp/epp.nominet.uk/700' && echo 'Port 700 open' || echo 'Port 700 blocked'"

# Allow port 700
ssh root@212.227.127.30 "firewall-cmd --permanent --add-port=700/tcp && firewall-cmd --reload"
```

### OpenSSL Missing
```bash
# Install OpenSSL
ssh root@212.227.127.30 "dnf install php-openssl -y && systemctl restart php-fpm"
```

## Nominet Credentials

### Testbed
- URL: https://testbed.nominet.uk/
- Purpose: Testing only
- EPP Host: testbed-epp.nominet.uk:700

### Production
- URL: https://www.nominet.uk/
- Purpose: Live registration
- EPP Host: epp.nominet.uk:700

## Pricing Example

| TLD | Registration | Transfer | Renewal |
|-----|--------------|----------|---------|
| .uk | £8.00 | £8.00 | £10.00 |
| .co.uk | £8.00 | £8.00 | £10.00 |
| .org.uk | £8.00 | £8.00 | £10.00 |

## Important Links

- **Repository**: https://github.com/AXYNUK/Nominet-EPP
- **Issues**: https://github.com/AXYNUK/Nominet-EPP/issues
- **Nominet Docs**: https://registrars.nominet.uk/
- **FOSSBilling**: https://portal.axyn.co.uk/admin

## File Locations

```
/var/www/fossbilling/src/library/Registrar/Adapter/
├── Nominet.php          # Main adapter (18KB)
└── manifest.json        # Extension metadata (730B)

/var/www/fossbilling/data/log/
└── application.log      # FOSSBilling logs
```

## Version Info

- **Current**: v1.0.0
- **Released**: 2025-01-13
- **License**: Apache-2.0

## Support

- Email: support@axyn.co.uk
- GitHub: https://github.com/AXYNUK/Nominet-EPP/issues
