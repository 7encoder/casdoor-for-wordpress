<?php
/**
 * Main Casdoor Plugin Class
 *
 * @package CasdoorWordPress
 */

// Prevent direct access
defined('ABSPATH') || exit;

/**
 * Class Casdoor
 * Main plugin class that handles initialization and hooks
 */
class Casdoor {
    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Single instance of the class
     *
     * @var Casdoor|null
     */
    private static $_instance = null;

    /**
     * Default settings
     *
     * @var array
     */
    protected $default_settings = array(
        'active'                    => 0,
        'client_id'                 => '',
        'client_secret'             => '',
        'backend'                   => '',
        'organization'              => 'built-in',
        'application'               => 'app-built-in',
        'redirect_to_dashboard'     => 0,
        'login_only'                => 0,
        'auto_sso'                  => 0,
        'woo_edit_account_redirect' => 0,
    );

    /**
     * Get the singleton instance
     *
     * @return Casdoor
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once CASDOOR_PLUGIN_DIR . 'includes/functions.php';
        require_once CASDOOR_PLUGIN_DIR . 'includes/class-casdoor-admin.php';
        require_once CASDOOR_PLUGIN_DIR . 'includes/class-casdoor-rewrites.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register styles and scripts
        add_action('wp_loaded', array($this, 'register_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Custom login redirect
        add_action('init', array($this, 'custom_login'));
        
        // Logout handler
        add_action('wp_logout', array($this, 'logout'));
        
        // Admin hooks
        add_action('admin_menu', array('Casdoor_Admin', 'add_menu'));
        add_action('admin_init', array('Casdoor_Admin', 'register_settings'));
    }

    /**
     * Register plugin assets
     */
    public function register_assets() {
        wp_register_style(
            'casdoor-admin',
            CASDOOR_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CASDOOR_PLUGIN_VERSION
        );
        
        wp_register_script(
            'casdoor-admin',
            CASDOOR_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-accordion'),
            CASDOOR_PLUGIN_VERSION,
            true
        );
    }

    /**
     * Enqueue plugin assets
     */
    public function enqueue_assets() {
        wp_enqueue_style('casdoor-admin');
        wp_enqueue_script('casdoor-admin');
    }

    /**
     * Redirect wp-login.php to Casdoor if plugin is active
     */
    public function custom_login() {
        global $pagenow;
        
        $activated = absint(casdoor_get_option('active'));
        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
        
        if ('wp-login.php' === $pagenow && 'logout' !== $action && $activated) {
            // Preserve the intended destination
            $redirect = casdoor_get_login_target_from_request();
            $url = casdoor_get_login_url($redirect);
            wp_safe_redirect($url);
            exit;
        }
    }

    /**
     * Handle WordPress logout with RP-initiated Casdoor logout
     */
    public function logout() {
        // Default redirect after logout
        $post_logout_redirect = home_url('/');

        // Check for custom redirect_to parameter
        if (!empty($_REQUEST['redirect_to'])) {
            $maybe_redirect = esc_url_raw((string) $_REQUEST['redirect_to']);
            $validated = wp_validate_redirect($maybe_redirect, $post_logout_redirect);
            if (!empty($validated)) {
                $post_logout_redirect = $validated;
            }
        }

        // Get Casdoor backend URL
        $backend = trim((string) casdoor_get_option('backend'));
        if ($backend === '') {
            wp_safe_redirect($post_logout_redirect);
            exit;
        }
        $backend = rtrim($backend, '/');

        // Retrieve access token stored during login
        $cookie_name = 'casdoor_access_token';
        $access_token = isset($_COOKIE[$cookie_name]) ? sanitize_text_field((string) $_COOKIE[$cookie_name]) : '';

        // Clear the cookie
        $cookie_domain = parse_url(home_url(), PHP_URL_HOST);
        $cookie_opts = array(
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => $cookie_domain ?: '',
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        );
        
        if (PHP_VERSION_ID >= 70300) {
            setcookie($cookie_name, '', $cookie_opts);
        } else {
            setcookie(
                $cookie_name,
                '',
                $cookie_opts['expires'],
                $cookie_opts['path'],
                $cookie_opts['domain'],
                $cookie_opts['secure'],
                $cookie_opts['httponly']
            );
        }

        // Perform RP-initiated logout if we have a token
        if (!empty($access_token)) {
            $logout_endpoint = $backend . '/api/logout';
            $logout_url = add_query_arg(
                array(
                    'id_token_hint'            => $access_token,
                    'post_logout_redirect_uri' => $post_logout_redirect,
                ),
                $logout_endpoint
            );
            wp_safe_redirect($logout_url);
            exit;
        }

        // Fallback: local logout only
        wp_safe_redirect($post_logout_redirect);
        exit;
    }

    /**
     * Get default settings
     *
     * @return array
     */
    public function get_default_settings() {
        return $this->default_settings;
    }
}
