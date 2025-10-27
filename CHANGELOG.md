# Changelog

All notable changes to the Casdoor WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-10-27

### Added
- Initial release of Casdoor WordPress Plugin
- Complete OAuth 2.0 Single Sign-On integration with Casdoor
- Secure authentication flow with JWT token validation
- RP-initiated logout (single sign-out)
- WordPress admin settings page with accordion UI
- Support for automatic user creation from Casdoor
- Login-only mode for restricting to existing WordPress users
- Auto SSO option for automatic redirect of unauthenticated users
- Shortcode `[casdoor_sso_button]` for custom login buttons
- WooCommerce integration features:
  - Automatic redirect of `/my-account/edit-account/` to Casdoor account page
  - "Create an Account" link on WooCommerce Block Checkout
  - Dynamic link rewriting with MutationObserver
  - Support for both `/my-account/edit-account/` and `/account/edit-account/` URLs
- Comprehensive security features:
  - HttpOnly, Secure, and SameSite cookie flags
  - CSRF protection with state parameter validation
  - Input sanitization and validation
  - SSL certificate verification enforced
  - XSS prevention with proper output escaping
  - Open redirect protection
  - SQL injection prevention via WordPress DB abstraction
- Helper functions for plugin integration:
  - `casdoor_get_login_url()` - Get Casdoor login URL
  - `casdoor_get_signup_url()` - Get Casdoor signup URL
  - `casdoor_get_option()` - Get plugin option value
  - `casdoor_set_option()` - Set plugin option value
- URL rewriting for clean auth endpoints
- Error message templates with WordPress styling
- Responsive admin UI with jQuery accordion
- Proper WordPress plugin structure following best practices
- PHP 7.4 - 8.3 compatibility
- WordPress 5.8+ compatibility
- Apache 2.0 license
- Comprehensive documentation in README.md

### Security
- All user input properly sanitized and validated
- HTTPS-only cookies with security flags
- State parameter validation to prevent CSRF attacks
- Safe redirect validation to prevent open redirects
- SSL verification enforced for production use
- Secure password generation for new users
- HttpOnly cookies to prevent XSS token theft

### Developer Features
- Composer support for dependency management
- PSR-4 autoloading structure
- WordPress Coding Standards compliance
- Extensible through WordPress hooks and filters
- Clean class-based architecture with singleton pattern
- Proper namespacing and file organization

## [Unreleased]

### Planned
- Translation support (.pot file generation)
- PHPUnit test suite
- GitHub Actions CI/CD workflow
- WordPress Plugin Directory submission
- Integration with Casdoor PHP SDK
- Support for multiple Casdoor organizations
- User role mapping from Casdoor to WordPress
- Custom redirect rules configuration
- Activity logging and audit trail
- Admin dashboard widget with SSO statistics

---

For more details about each release, see the [GitHub Releases](https://github.com/7encoder/casdoor-for-wordpress/releases) page.
