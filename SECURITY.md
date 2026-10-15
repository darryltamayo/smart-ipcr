# 🔒 Security Guidelines

## Important Security Information

This project contains sensitive configuration that must be secured properly.

## ⚠️ Before Deploying

### 1. Configuration Files

**NEVER commit these files with real credentials:**
- ❌ `config/database.php` - Contains database passwords
- ❌ `config/email.php` - Contains email passwords

**Always use example templates:**
- ✅ `config/database.example.php` - Template for database config
- ✅ `config/email.config.example.php` - Template for email config

### 2. Setup Process

```bash
# Copy example files
cp config/database.example.php config/database.php
cp config/email.config.example.php config/email.php

# Edit with your credentials
nano config/database.php
nano config/email.php

# Make sure they're ignored
git status  # Should NOT show database.php or email.php
```

### 3. API Keys

All API keys are stored in database (`system_config` table), not in code:
- ✅ Gemini API Key
- ✅ OpenRouter API Key

**Configure via Admin Panel:**
```
Admin → Settings → AI Evidence Verification
```

### 4. Environment Variables (Recommended)

For production, consider using environment variables:

```php
// Example: Use environment variables
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'ipcr_system');
```

## 🛡️ Security Best Practices

### Database Security

1. **Use Strong Passwords**
   - Minimum 12 characters
   - Mix of uppercase, lowercase, numbers, symbols
   - Never use default passwords in production

2. **Restrict Database Access**
   - Create dedicated database user (not root)
   - Grant only necessary privileges
   - Limit connections to localhost

```sql
-- Create dedicated user
CREATE USER 'ipcr_user'@'localhost' IDENTIFIED BY 'strong_password_here';

-- Grant minimal privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON ipcr_system.* TO 'ipcr_user'@'localhost';
FLUSH PRIVILEGES;
```

3. **Backup Regularly**
   - Automated daily backups
   - Store backups securely
   - Test restore procedures

### Email Security

1. **Use App Passwords (Gmail)**
   - Never use your actual Gmail password
   - Generate App Password: https://myaccount.google.com/apppasswords
   - Revoke if compromised

2. **Enable 2FA**
   - Add extra security layer
   - Protect email account access

### File Upload Security

1. **Restrict File Types**
   - Only allow: PDF, DOC, DOCX, JPG, PNG
   - Block executables: .exe, .php, .sh

2. **Store Outside Web Root**
   - Uploads should not be web-accessible
   - Serve via PHP script only

3. **Scan Uploads**
   - Use antivirus/malware scanner
   - Validate file contents

### Session Security

1. **Secure Session Settings**
   ```php
   ini_set('session.cookie_httponly', 1);
   ini_set('session.cookie_secure', 1);  // HTTPS only
   ini_set('session.use_strict_mode', 1);
   ```

2. **Regenerate Session IDs**
   - After login
   - After privilege change
   - Periodically

### Password Security

✅ **Already Implemented:**
- Password hashing (bcrypt)
- Minimum length enforcement
- Failed login attempt tracking
- Account lockout mechanism

### HTTPS / SSL

**REQUIRED for production:**
```
✅ Use HTTPS for all traffic
✅ Force SSL redirect
✅ Use valid SSL certificate (Let's Encrypt is free)
```

`.htaccess` example:
```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 🔐 After Installation Checklist

- [ ] Changed all default passwords
- [ ] Created `config/database.php` from example
- [ ] Created `config/email.php` from example
- [ ] Verified `.gitignore` is working
- [ ] Set proper file permissions (644 for files, 755 for directories)
- [ ] Enabled HTTPS
- [ ] Configured firewall rules
- [ ] Set up automated backups
- [ ] Tested password reset flow
- [ ] Tested account lockout
- [ ] Reviewed audit logs
- [ ] Changed admin default password

## 🚨 If Credentials Are Compromised

1. **Immediately Change:**
   - Database password
   - Email password (generate new app password)
   - Admin user passwords
   - API keys

2. **Revoke Access:**
   - Regenerate all API keys
   - Revoke old app passwords
   - Force logout all users

3. **Audit:**
   - Check audit logs for suspicious activity
   - Review recent changes
   - Verify no unauthorized access

4. **Update:**
   - Update all server configurations
   - Clear all sessions
   - Notify affected users

## 📞 Security Contact

For security issues, contact:
- Email: security@isu.edu.ph
- Do not disclose publicly until fixed

## 🔗 Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [MySQL Security Guidelines](https://dev.mysql.com/doc/refman/8.0/en/security-guidelines.html)
- [Let's Encrypt (Free SSL)](https://letsencrypt.org/)

---

**Remember:** Security is an ongoing process, not a one-time setup!
