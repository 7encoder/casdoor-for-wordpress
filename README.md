# Casdoor WordPress Plugin

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-%3E%3D5.8-blue.svg)](https://wordpress.org/)

A modern, secure WordPress plugin for Single Sign-On (SSO) authentication using [Casdoor](https://casdoor.org/). This plugin seamlessly integrates Casdoor authentication into WordPress and WooCommerce, following the latest WordPress development guidelines and security best practices.

## Features

- 🔐 **Secure OAuth 2.0 Authentication** - Full SSO integration with Casdoor
- 🛡️ **Security First** - Built with WordPress security best practices, HTTPS-only cookies, CSRF protection
- 🛒 **WooCommerce Integration** - Redirect account edit pages to Casdoor, checkout signup links
- 🎨 **Modern WordPress Standards** - Follows WordPress Coding Standards and Plugin Handbook guidelines
- 🔄 **RP-Initiated Logout** - Proper single sign-out from both WordPress and Casdoor
- 🌐 **PHP 8.3 Compatible** - Works with PHP 7.4 through PHP 8.3
- ⚡ **Auto SSO** - Optional automatic redirect to require authentication for all site visitors
- 🎯 **Flexible User Management** - Login-only mode or automatic user creation
- 🔌 **Shortcode Support** - Easy SSO button integration with `[casdoor_sso_button]`

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher (tested up to PHP 8.3)
- A running Casdoor instance
- HTTPS recommended for production use

## Installation

### Manual Installation

1. Download the plugin files
2. Upload the `casdoor-wordpress-plugin` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the plugin in Settings → Casdoor SSO

### Via Composer

```bash
composer require casdoor/wordpress-casdoor-plugin
```

## Configuration

### Step 1: Configure Casdoor

1. Install and run [Casdoor](https://github.com/casdoor/casdoor)
2. Create a new application in Casdoor
3. Add your WordPress callback URL to the application's Redirect URLs:
   ```
   https://your-wordpress-site.com/?auth=casdoor
   ```
4. Note your Client ID and Client Secret

### Step 2: Configure WordPress Plugin

1. Navigate to **Settings → Casdoor SSO** in WordPress admin
2. Configure the following settings:

   - **Activate Casdoor**: Enable SSO authentication
   - **Client ID**: Your Casdoor application Client ID
   - **Client Secret**: Your Casdoor application Client Secret
   - **Backend URL**: Your Casdoor server URL (e.g., `https://casdoor.example.com`)
   - **Organization**: Casdoor organization name (default: `built-in`)
   - **Application**: Casdoor application name (default: `app-built-in`)

3. Optional settings:
   - **Redirect to Dashboard**: Redirect users to admin dashboard after login
   - **Login Only Mode**: Restrict login to existing WordPress users only
   - **Auto SSO**: When enabled, any visitor who is not logged in will be automatically redirected to Casdoor to authenticate. This effectively makes your entire WordPress site require authentication—visitors must log in before they can access any page.
   - **WooCommerce Integration**: Enable WooCommerce-specific features

## Usage

### Basic Login

Once configured, all login attempts to `wp-login.php` will redirect to Casdoor. Users can also access SSO login via:

```
https://your-site.com/?auth=casdoor
```

### Shortcode

Add a login button anywhere on your site:

```php
[casdoor_sso_button text="Sign in with Casdoor" class="my-custom-class"]
```

Attributes:
- `text` - Button text (default: "Casdoor Single Sign-On")
- `class` - CSS class (default: "sso-button")
- `title` - Link title attribute
- `target` - Link target (default: "_blank")

### Programmatic Login URL

Get the Casdoor login URL in your theme or plugin:

```php
$login_url = casdoor_get_login_url($redirect_after_login);
```

## WooCommerce Integration

When WooCommerce integration is enabled:

1. **Account Edit Redirect**: Links to `/my-account/edit-account/` are rewritten to point to Casdoor's account page
2. **Checkout Signup Link**: A "Create an Account" link appears on checkout for guest users
3. **Secure Links**: All external links open in new tabs with `noopener noreferrer` attributes

## Security Features

- ✅ **HTTPS-Only Cookies**: Secure cookie handling with HttpOnly and SameSite flags
- ✅ **CSRF Protection**: State parameter validation in OAuth flow
- ✅ **Input Sanitization**: All user inputs properly sanitized and validated
- ✅ **SQL Injection Prevention**: Uses WordPress database abstraction layer
- ✅ **XSS Prevention**: Output escaping with WordPress functions
- ✅ **Open Redirect Protection**: Validates all redirect URLs
- ✅ **SSL Verification**: Enforces SSL certificate verification in production

## Troubleshooting

### Login Redirects Not Working

1. Go to **Settings → Permalinks** and click "Save Changes" to flush rewrite rules
2. Verify your Casdoor callback URL matches exactly: `https://your-site.com/?auth=casdoor`

### Users Not Being Created

- Check that "Login Only Mode" is disabled if you want automatic user creation
- Verify the Casdoor user has a valid email address

### SSL/HTTPS Errors

- Ensure your Casdoor server has a valid SSL certificate
- For development, you can temporarily disable SSL verification (not recommended for production)

## Development

### Running Tests

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage
composer test:coverage
```

### Code Standards

This plugin follows:
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- PHP-FIG PSR-12 where applicable

## Workflow

### Authentication Flow

1. User attempts to access WordPress login
2. Plugin redirects to Casdoor authorization endpoint
3. User authenticates with Casdoor
4. Casdoor redirects back with authorization code
5. Plugin exchanges code for access token
6. Plugin decodes JWT to get user information
7. Plugin creates or finds WordPress user
8. User is logged into WordPress

### Logout Flow

1. User clicks logout in WordPress
2. Plugin clears local session and WordPress cookies
3. Plugin redirects to Casdoor logout endpoint (RP-initiated logout)
4. Casdoor clears its session
5. User is redirected back to WordPress home page

## Hooks and Filters

### Actions

```php
// After successful Casdoor login
do_action('wp_login', $user_login, $user);
```

### Filters

```php
// Modify allowed redirect hosts
add_filter('allowed_redirect_hosts', function($hosts) {
    $hosts[] = 'your-casdoor-domain.com';
    return $hosts;
});
```

## Changelog

### Version 1.0.0
- Initial release
- Full OAuth 2.0 SSO integration with Casdoor
- WooCommerce support
- RP-initiated logout
- PHP 8.3 compatibility
- Security hardening
- WordPress 6.x compatibility

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

- [GitHub Issues](https://github.com/7encoder/casdoor-for-wordpress/issues)
- [Casdoor Documentation](https://casdoor.org/docs/)
- [WordPress Support Forums](https://wordpress.org/support/)

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE) file for details.

## Credits

Developed for the [Casdoor](https://casdoor.org/) project.

## Links

- [Casdoor GitHub](https://github.com/casdoor/casdoor)
- [WordPress Plugin Directory](https://wordpress.org/plugins/)
- [WooCommerce](https://woocommerce.com/)