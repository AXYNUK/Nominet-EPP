# Installation Guide - Nominet EPP for FOSSBilling

This guide walks you through installing and configuring the Nominet EPP registrar adapter.

## Prerequisites Checklist

Before installation, ensure you have:

- [ ] FOSSBilling 0.6.0+ installed and running
- [ ] PHP 7.4+ with OpenSSL extension enabled
- [ ] Nominet IPS Tag registered
- [ ] Nominet EPP credentials generated
- [ ] Admin access to your FOSSBilling installation

## Step 1: Verify PHP Requirements

SSH into your server and run:

```bash
# Check PHP version
php -v

# Verify OpenSSL extension
php -m | grep openssl

# Should output: openssl
```

If OpenSSL is missing:

```bash
# Rocky/CentOS/RHEL
sudo dnf install php-openssl
sudo systemctl restart php-fpm

# Ubuntu/Debian
sudo apt install php-openssl
sudo systemctl restart php-fpm
```

## Step 2: Obtain Nominet Credentials

### Production Credentials

1. Visit [Nominet.uk](https://www.nominet.uk/)
2. Register as a member/registrar
3. Apply for an IPS Tag (e.g., "AXYN")
4. Navigate to your Nominet account dashboard
5. Go to **EPP Access** → **Credentials**
6. Generate EPP password
7. Note your IPS Tag and password

### Testbed Credentials (for testing)

1. Visit [Nominet Testbed](https://testbed.nominet.uk/)
2. Register for testbed access
3. Obtain testbed IPS Tag
4. Generate testbed EPP credentials

## Step 3: Install the Extension

### Option A: Download from GitHub Releases

```bash
# Download latest release
cd /tmp
wget https://github.com/AXYNUK/Nominet-EPP/releases/latest/download/Nominet.zip

# Extract to FOSSBilling adapters directory
sudo unzip Nominet.zip -d /var/www/fossbilling/src/library/Registrar/Adapter/

# Set correct permissions
sudo chown -R nginx:nginx /var/www/fossbilling/src/library/Registrar/Adapter/Nominet.php
sudo chmod 644 /var/www/fossbilling/src/library/Registrar/Adapter/Nominet.php
```

### Option B: Clone from GitHub

```bash
# Clone repository
cd /tmp
git clone https://github.com/AXYNUK/Nominet-EPP.git

# Copy adapter to FOSSBilling
sudo cp Nominet-EPP/Nominet.php /var/www/fossbilling/src/library/Registrar/Adapter/

# Set permissions
sudo chown nginx:nginx /var/www/fossbilling/src/library/Registrar/Adapter/Nominet.php
sudo chmod 644 /var/www/fossbilling/src/library/Registrar/Adapter/Nominet.php
```

### Option C: Via FOSSBilling Admin Panel

1. Download `Nominet.zip` from GitHub releases
2. Log in to FOSSBilling admin panel
3. Navigate to **System** → **Extensions**
4. Click **Upload Extension**
5. Select the ZIP file
6. Click **Install**

## Step 4: Configure Nominet Registrar

1. Log in to FOSSBilling admin panel
2. Navigate to **System** → **Domain Registration** → **Registrars**
3. Find **Nominet EPP** in the list
4. Click **Configure**
5. Enter configuration:

   ```
   Nominet Tag (IPS Tag): YOUR_IPS_TAG
   Password: YOUR_EPP_PASSWORD
   Test Mode: ☑ (enable for testing)
   ```

6. Click **Save**

## Step 5: Enable TLDs

1. Still in **Domain Registration** → **Registrars**
2. Click **TLDs** tab
3. Enable the TLDs you want to offer:
   - `.uk`
   - `.co.uk`
   - `.org.uk`
   - `.me.uk`
   - `.net.uk`
   - `.ltd.uk`
   - `.plc.uk`
   - `.sch.uk`

4. For each TLD, set:
   - **Registrar**: Nominet EPP
   - **Price**: Your selling price
   - **Transfer Price**: Your transfer price
   - **Renewal Price**: Your renewal price

## Step 6: Test Configuration

### Test Domain Availability

1. Navigate to **Domain Registration** → **Check Domain**
2. Enter a test domain (e.g., `test123456.co.uk`)
3. Click **Check**
4. Should return availability status

### Test Domain Registration (Testbed Only)

If using testbed:

1. Find an available domain in testbed
2. Register it through FOSSBilling
3. Verify registration in Nominet testbed dashboard
4. Check FOSSBilling logs for errors

### Check Logs

```bash
# FOSSBilling logs
tail -f /var/www/fossbilling/data/log/application.log

# Nginx logs
tail -f /var/log/nginx/error.log
```

## Step 7: Switch to Production

Once testing is successful:

1. Navigate to **System** → **Domain Registration** → **Registrars**
2. Click **Configure** on Nominet EPP
3. **Uncheck** Test Mode
4. Enter production credentials:
   ```
   Nominet Tag (IPS Tag): YOUR_PRODUCTION_IPS_TAG
   Password: YOUR_PRODUCTION_EPP_PASSWORD
   Test Mode: ☐ (disabled)
   ```
5. Click **Save**

## Step 8: Configure Pricing

1. Navigate to **System** → **Products/Services**
2. Create new product:
   - **Type**: Domain Registration
   - **Registrar**: Nominet EPP
   - **TLDs**: Select .uk domains
   
3. Set pricing (example):
   ```
   Registration (1 year): £8.00
   Transfer (1 year): £8.00
   Renewal (1 year): £10.00
   ```

## Troubleshooting

### Error: "OpenSSL extension not loaded"

```bash
# Install OpenSSL extension
sudo dnf install php-openssl
sudo systemctl restart php-fpm
```

### Error: "Connection timed out"

Check firewall allows outbound port 700:

```bash
# Check if port is blocked
sudo firewall-cmd --list-all

# Allow port 700 outbound (if needed)
sudo firewall-cmd --permanent --add-port=700/tcp
sudo firewall-cmd --reload
```

### Error: "EPP Login failed"

- Verify IPS Tag is correct
- Verify password is correct
- Check if using correct environment (test vs production)
- Ensure IP is whitelisted with Nominet

### Error: "Domain registration failed"

- Check domain is actually available
- Verify nameservers are valid
- Check Nominet account balance
- Review application.log for details

## Verification Checklist

After installation, verify:

- [ ] Extension appears in registrar list
- [ ] Configuration saves successfully
- [ ] Domain availability checks work
- [ ] Test registration completes (in testbed)
- [ ] Domain details can be retrieved
- [ ] Nameserver updates work
- [ ] No errors in application.log
- [ ] Production credentials configured
- [ ] Pricing configured for all TLDs

## Next Steps

1. **Create domain products** for your customers
2. **Set up automated renewals** in FOSSBilling
3. **Configure WHMCS integration** (if needed)
4. **Set up monitoring** for EPP connection health
5. **Train staff** on domain management

## Support

Need help? Contact:

- GitHub Issues: <https://github.com/AXYNUK/Nominet-EPP/issues>
- Email: support@axyn.co.uk
- Nominet Support: <https://www.nominet.uk/support/>

## Advanced Configuration

### Whitelisting IPs with Nominet

Nominet may require you to whitelist your server IP:

1. Log in to Nominet account
2. Navigate to **EPP Access** → **IP Whitelist**
3. Add your server IP: `212.227.127.30`
4. Save and wait 15 minutes for propagation

### Setting Up Automated Testing

Create a cron job to test EPP connectivity:

```bash
# Create test script
sudo nano /usr/local/bin/test-nominet-epp.php
```

```php
<?php
require_once '/var/www/fossbilling/bb-load.php';

$di = include '/var/www/fossbilling/di.php';
$service = $di['mod_service']('servicedomain');

// Test domain availability
try {
    $result = $service->isDomainAvailable('test-'.time().'.co.uk');
    echo "EPP Test: OK\n";
} catch (Exception $e) {
    echo "EPP Test: FAILED - " . $e->getMessage() . "\n";
}
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/test-nominet-epp.php

# Add to crontab (hourly test)
sudo crontab -e
```

Add line:
```
0 * * * * /usr/bin/php /usr/local/bin/test-nominet-epp.php >> /var/log/nominet-epp-test.log 2>&1
```

### Monitoring EPP Performance

Monitor EPP response times:

```bash
# Check recent EPP operations in logs
grep "Nominet EPP" /var/www/fossbilling/data/log/application.log | tail -20
```

## Security Best Practices

1. **Secure credentials** - Store EPP password in config, not database
2. **Limit access** - Restrict admin access to domain management
3. **Monitor logs** - Check for unauthorized access attempts
4. **Regular audits** - Review domain registrations monthly
5. **Backup data** - Include domain database in backups
6. **Update regularly** - Keep FOSSBilling and extension updated

## Upgrading

To upgrade to a newer version:

```bash
# Backup current installation
sudo cp /var/www/fossbilling/src/library/Registrar/Adapter/Nominet.php \
       /var/www/fossbilling/src/library/Registrar/Adapter/Nominet.php.backup

# Download new version
cd /tmp
wget https://github.com/AXYNUK/Nominet-EPP/releases/latest/download/Nominet.zip

# Replace file
sudo unzip -o Nominet.zip -d /var/www/fossbilling/src/library/Registrar/Adapter/

# Set permissions
sudo chown nginx:nginx /var/www/fossbilling/src/library/Registrar/Adapter/Nominet.php

# Clear FOSSBilling cache
sudo rm -rf /var/www/fossbilling/data/cache/*

# Test
# (perform domain availability check in admin panel)
```

## Uninstallation

To remove the extension:

```bash
# Remove adapter file
sudo rm /var/www/fossbilling/src/library/Registrar/Adapter/Nominet.php

# Clear cache
sudo rm -rf /var/www/fossbilling/data/cache/*
```

Note: This will not affect existing domains, but you won't be able to manage them through FOSSBilling.

---

**Installation Complete!** You're now ready to register .uk domains through FOSSBilling.
