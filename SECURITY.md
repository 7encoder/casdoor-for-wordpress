# Security Policy

## Supported Versions

We release patches for security vulnerabilities. Currently supported versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Security Features

This plugin implements multiple layers of security:

### Authentication Security

- **OAuth 2.0 Protocol**: Industry-standard authentication flow
- **JWT Token Validation**: Secure token parsing and validation
- **State Parameter**: CSRF protection in OAuth flow
- **Secure Redirects**: All redirects validated against open redirect attacks

### Cookie Security

- **HttpOnly Flag**: Prevents JavaScript access to authentication cookies
- **Secure Flag**: Cookies only sent over HTTPS in production
- **SameSite=Lax**: Protection against CSRF attacks
- **Domain Scoping**: Cookies limited to your domain

### Input Validation

- **Sanitization**: All user input sanitized using WordPress functions
- **Validation**: Input validation before processing
- **Type Checking**: Strict type validation on all parameters
- **Escaping**: All output properly escaped to prevent XSS

### Database Security

- **Prepared Statements**: All database queries use WordPress DB abstraction
- **No Direct Queries**: No raw SQL queries
- **SQL Injection Prevention**: WordPress handles all data escaping

### SSL/TLS

- **Certificate Verification**: SSL certificates always verified in production
- **HTTPS Only**: Secure cookies require HTTPS
- **Modern TLS**: Supports TLS 1.2 and higher

### WordPress Security

- **Nonce Validation**: WordPress nonces used where applicable
- **Capability Checks**: Admin functions restricted to appropriate users
- **Direct Access Prevention**: All files check for ABSPATH
- **Safe Redirects**: Uses wp_safe_redirect() for all redirects

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security issue, please follow these steps:

### Do NOT

- ❌ Create a public GitHub issue
- ❌ Disclose the vulnerability publicly
- ❌ Exploit the vulnerability

### DO

1. **Email Security Team**
   - Send details to: security@casdoor.org
   - Include "SECURITY: Casdoor WordPress Plugin" in subject

2. **Provide Details**
   ```
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Affected versions
   - Suggested fix (if any)
   ```

3. **Wait for Response**
   - We aim to respond within 48 hours
   - We'll work with you on a fix
   - We'll coordinate disclosure timeline

### What to Expect

- **Acknowledgment**: Within 48 hours
- **Assessment**: Within 1 week
- **Fix Timeline**: Varies by severity
  - Critical: 1-7 days
  - High: 1-2 weeks
  - Medium: 2-4 weeks
  - Low: Next release

### Disclosure Process

1. **Private Fix**: Develop and test fix privately
2. **Security Release**: Release patch version
3. **Coordinated Disclosure**: Announce vulnerability after patch is available
4. **Credit**: Security researchers credited (if desired)

## Security Best Practices

### For Site Administrators

1. **Use HTTPS**
   ```
   ✓ Always use SSL/TLS certificates in production
   ✓ Enable HTTPS in WordPress settings
   ✓ Redirect HTTP to HTTPS
   ```

2. **Keep Updated**
   ```
   ✓ Update WordPress core regularly
   ✓ Update this plugin when updates available
   ✓ Update Casdoor server regularly
   ```

3. **Strong Credentials**
   ```
   ✓ Use strong passwords for WordPress admin
   ✓ Use strong passwords for Casdoor accounts
   ✓ Keep Client Secret secure
   ✓ Rotate secrets periodically
   ```

4. **Monitor Activity**
   ```
   ✓ Review login logs regularly
   ✓ Monitor for suspicious activity
   ✓ Enable WordPress security plugins
   ```

5. **Restrict Access**
   ```
   ✓ Limit who has WordPress admin access
   ✓ Use Login Only Mode if appropriate
   ✓ Review user permissions regularly
   ```

### For Developers

1. **Code Review**
   ```php
   // Always sanitize input
   $input = sanitize_text_field($_GET['input']);
   
   // Always escape output
   echo esc_html($user_data);
   
   // Always validate redirects
   $redirect = wp_validate_redirect($url, $default);
   ```

2. **Testing**
   ```
   ✓ Test all code changes
   ✓ Test with different PHP versions
   ✓ Test with different WordPress versions
   ✓ Test security features
   ```

3. **Dependencies**
   ```
   ✓ Keep dependencies updated
   ✓ Review dependency security advisories
   ✓ Use composer for dependency management
   ```

## Security Checklist

Before deploying to production:

- [ ] HTTPS enabled and enforced
- [ ] Valid SSL certificate installed
- [ ] Client Secret kept secure
- [ ] Callback URL correctly configured in Casdoor
- [ ] WordPress and plugin updated to latest versions
- [ ] File permissions correctly set (755 directories, 644 files)
- [ ] Debug mode disabled
- [ ] Error reporting disabled in production
- [ ] Database credentials secure
- [ ] Admin account uses strong password
- [ ] Two-factor authentication enabled (if available)
- [ ] Regular backups configured
- [ ] Security plugin installed (recommended)
- [ ] Login attempt limiting configured

## Known Security Considerations

### Cookie Storage

The plugin stores an access token in a cookie for RP-initiated logout. This token:
- Is HttpOnly (not accessible via JavaScript)
- Is Secure (only sent over HTTPS)
- Has SameSite=Lax (CSRF protection)
- Expires after 24 hours
- Is only used for logout

This is the recommended approach for RP-initiated logout in OAuth 2.0.

### User Creation

When a new user logs in via Casdoor:
- A random strong password is generated
- The user cannot login with this password (SSO only)
- The password is never displayed or sent
- User email is taken from Casdoor (must be unique)

### Admin Users

If a Casdoor user has `isGlobalAdmin` flag:
- They are created as WordPress Administrator
- This should only be used for trusted users
- Review admin permissions regularly

### State Parameter

The OAuth state parameter:
- Contains the post-login redirect URL
- Is validated on return
- Prevents CSRF attacks
- Validates same-origin redirects only

## Compliance

This plugin follows:
- OWASP Top 10 security guidelines
- WordPress Security Best Practices
- OAuth 2.0 Security Best Practices
- GDPR principles (when properly configured)

## Security Updates

Subscribe to security updates:
- Watch this repository on GitHub
- Enable GitHub security advisories
- Follow @casdoor on social media

## Third-Party Security Tools

Recommended security plugins:
- Wordfence Security
- Sucuri Security
- iThemes Security
- All In One WP Security & Firewall

## License

Security policies are part of the Apache 2.0 license.

## Contact

For security inquiries:
- Email: security@casdoor.org
- PGP Key: Available on request
- Response Time: 48 hours

---

Last Updated: 2025-10-27
