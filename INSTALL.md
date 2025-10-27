# Installation Guide

This guide will walk you through installing and configuring the Casdoor WordPress Plugin.

## Prerequisites

Before you begin, ensure you have:

1. **WordPress Installation**
   - WordPress 5.8 or higher
   - Admin access to your WordPress site
   - HTTPS enabled (recommended for production)

2. **PHP Requirements**
   - PHP 7.4 or higher
   - PHP 8.0, 8.1, 8.2, or 8.3 supported
   - Required PHP extensions: `json`, `curl`, `openssl`

3. **Casdoor Instance**
   - A running Casdoor server (see [Casdoor Documentation](https://casdoor.org/docs/basic/server-installation))
   - Admin access to create applications

## Installation Steps

### Option 1: Manual Installation

1. **Download the Plugin**
   ```bash
   # Clone or download from GitHub
   git clone https://github.com/7encoder/casdoor-for-wordpress.git
   ```

2. **Upload to WordPress**
   - Extract the plugin folder
   - Upload the entire `casdoor-wordpress-plugin` folder to `/wp-content/plugins/`
   - Or use WordPress admin: Plugins → Add New → Upload Plugin

3. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Casdoor WordPress Plugin"
   - Click "Activate"

### Option 2: Via Composer

```bash
cd /path/to/wordpress/wp-content/plugins/
composer require casdoor/wordpress-casdoor-plugin
```

Then activate via WordPress admin.

## Casdoor Configuration

### Step 1: Create Application in Casdoor

1. Log in to your Casdoor admin panel
2. Navigate to **Applications** → **Add Application**
3. Fill in the application details:
   - **Name**: WordPress (or your site name)
   - **Display Name**: WordPress SSO
   - **Organization**: Select your organization (default: `built-in`)

4. **Configure Redirect URLs**:
   Add your WordPress callback URL:
   ```
   https://your-wordpress-site.com/?auth=casdoor
   ```
   
   Replace `your-wordpress-site.com` with your actual domain.

5. **Save the Application**
6. **Note the credentials**:
   - Client ID
   - Client Secret
   
   Keep these secure - you'll need them for WordPress configuration.

### Step 2: Configure Plugin in WordPress

1. **Navigate to Settings**
   - Go to WordPress Admin → Settings → Casdoor SSO
   - Or directly access `/wp-admin/options-general.php?page=casdoor-settings`

2. **Basic Configuration**
   
   Fill in the following required fields:
   
   - **Activate Casdoor**: ☑ Check to enable
   - **Client ID**: Paste from Casdoor application
   - **Client Secret**: Paste from Casdoor application
   - **Backend URL**: Your Casdoor server URL
     ```
     Example: https://casdoor.example.com
     ```
   - **Organization**: Your Casdoor organization name
     ```
     Default: built-in
     ```
   - **Application**: Your Casdoor application name
     ```
     Default: app-built-in
     ```

3. **Optional Settings**

   Configure these based on your needs:
   
   - **Redirect to Dashboard**: ☐ Redirect users to admin dashboard after login
   - **Login Only Mode**: ☐ Only allow existing WordPress users to login
   - **Auto SSO**: ☐ Automatically redirect non-logged-in users to Casdoor
   - **WooCommerce Integration**: ☐ Enable WooCommerce-specific features

4. **Save Changes**
   - Click "Save Changes" button
   - The plugin will validate your settings

### Step 3: Test the Integration

1. **Open New Incognito Window**
   - This prevents interference with your current session
   
2. **Test Login**
   - Go to your WordPress site
   - Try to access wp-login.php
   - You should be redirected to Casdoor
   
3. **Authenticate with Casdoor**
   - Enter your Casdoor credentials
   - Click login
   
4. **Verify Redirect**
   - You should be redirected back to WordPress
   - You should be logged in automatically
   - Check your WordPress user account was created

## Advanced Configuration

### Custom Login Link

You can add SSO login links anywhere on your site:

```html
<a href="https://your-site.com/?auth=casdoor">Login with SSO</a>
```

Or use the shortcode:

```php
[casdoor_sso_button text="Sign in with Casdoor"]
```

### Programmatic Access

In themes or plugins:

```php
// Get login URL
$login_url = casdoor_get_login_url('/desired-redirect-path');

// Get signup URL
$signup_url = casdoor_get_signup_url('custom-application-name');

// Get option value
$backend = casdoor_get_option('backend');
```

## WooCommerce Setup (Optional)

If you have WooCommerce installed:

1. **Enable Integration**
   - Go to Settings → Casdoor SSO
   - Check "WooCommerce Integration"
   - Save changes

2. **Features Enabled**:
   - Account edit pages redirect to Casdoor
   - Signup links on checkout
   - Protected account pages

## Troubleshooting

### Redirect Loop

If you experience a redirect loop:

1. **Flush Rewrite Rules**
   - Go to Settings → Permalinks
   - Click "Save Changes" (no changes needed)

2. **Check Callback URL**
   - Verify the callback URL in Casdoor exactly matches:
     ```
     https://your-site.com/?auth=casdoor
     ```

### Users Not Created

If users aren't being created automatically:

1. **Disable Login Only Mode**
   - Go to Settings → Casdoor SSO
   - Uncheck "Login Only Mode"
   - Save changes

2. **Check Casdoor User Email**
   - Ensure Casdoor user has a valid email address

### SSL Certificate Errors

For development environments only:

- The plugin enforces SSL verification by default
- For production, always use valid SSL certificates
- Never disable SSL verification in production

### Plugin Not Appearing

If plugin doesn't show in admin:

1. **Check File Permissions**
   ```bash
   chmod 755 /wp-content/plugins/casdoor-wordpress-plugin
   chmod 644 /wp-content/plugins/casdoor-wordpress-plugin/*.php
   ```

2. **Check PHP Version**
   ```bash
   php --version
   # Should be 7.4 or higher
   ```

3. **Check PHP Errors**
   - Enable WordPress debug mode
   - Check wp-content/debug.log

## Next Steps

- [Read the full documentation](README.md)
- [Configure WooCommerce integration](README.md#woocommerce-integration)
- [Review security features](README.md#security-features)
- [Report issues on GitHub](https://github.com/7encoder/casdoor-for-wordpress/issues)

## Support

- GitHub Issues: https://github.com/7encoder/casdoor-for-wordpress/issues
- Casdoor Documentation: https://casdoor.org/docs/
- WordPress Support: https://wordpress.org/support/

## Security Notes

- Always use HTTPS in production
- Keep Client Secret secure
- Regularly update WordPress and the plugin
- Use strong passwords for Casdoor accounts
- Monitor login activity

---

Need help? Open an issue on [GitHub](https://github.com/7encoder/casdoor-for-wordpress/issues).
