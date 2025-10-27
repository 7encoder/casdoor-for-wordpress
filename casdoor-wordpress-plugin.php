<?php
/**
 * Plugin Name: Casdoor WordPress Plugin
 * Plugin URI: https://github.com/7encoder/casdoor-for-wordpress
 * Description: Enable Single Sign-On authentication using Casdoor with full WordPress and WooCommerce integration
 * Version: 1.0.0
 * Author: Casdoor
 * Author URI: https://github.com/casdoor/
 * License: Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: casdoor-wordpress-plugin
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * 
 * @package CasdoorWordPress
 */

// Prevent direct access to this file
defined('ABSPATH') || exit;

// Define plugin constants
if (!defined('CASDOOR_PLUGIN_VERSION')) {
    define('CASDOOR_PLUGIN_VERSION', '1.0.0');
}

if (!defined('CASDOOR_PLUGIN_DIR')) {
    define('CASDOOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('CASDOOR_PLUGIN_URL')) {
    define('CASDOOR_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('CASDOOR_PLUGIN_BASENAME')) {
    define('CASDOOR_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

// Require the main plugin class
require_once CASDOOR_PLUGIN_DIR . 'includes/class-casdoor.php';

/**
 * Initialize the plugin
 */
function casdoor_init() {
    Casdoor::instance();
}
add_action('plugins_loaded', 'casdoor_init');

/**
 * Activation hook
 */
function casdoor_activate() {
    // Flush rewrite rules on activation
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'casdoor_activate');

/**
 * Deactivation hook
 */
function casdoor_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'casdoor_deactivate');
