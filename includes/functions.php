<?php
/**
 * Helper Functions
 *
 * @package CasdoorWordPress
 */

// Prevent direct access
defined('ABSPATH') || exit;

/**
 * Get default options
 *
 * @return array
 */
function casdoor_defaults() {
    return array(
        'client_id'                 => '',
        'client_secret'             => '',
        'backend'                   => '',
        'organization'              => 'built-in',
        'application'               => 'app-built-in',
        'redirect_to_dashboard'     => 0,
        'login_only'                => 0,
        'auto_sso'                  => 0,
        'active'                    => 0,
        'woo_edit_account_redirect' => 0,
    );
}

/**
 * Get all Casdoor options
 *
 * @return array
 */
function casdoor_get_options_internal() {
    $options = get_option('casdoor_options', array());
    if (!is_array($options)) {
        $options = casdoor_defaults();
    }
    $options = array_merge(casdoor_defaults(), $options);
    return $options;
}

/**
 * Get a specific Casdoor option value
 *
 * @param string $option_name Option name
 * @return mixed|null
 */
function casdoor_get_option($option_name) {
    $options = casdoor_get_options_internal();
    return isset($options[$option_name]) ? $options[$option_name] : null;
}

/**
 * Set a Casdoor option value
 *
 * @param string $key   Option key
 * @param mixed  $value Option value
 */
function casdoor_set_option($key, $value) {
    $options = casdoor_get_options_internal();
    $options[$key] = $value;
    update_option('casdoor_options', $options);
}

/**
 * Get the Casdoor login URL
 *
 * @param string $redirect URL to redirect to after login (stored in state)
 * @return string
 */
function casdoor_get_login_url($redirect = '') {
    $params = array(
        'oauth'         => 'authorize',
        'response_type' => 'code',
        'client_id'     => casdoor_get_option('client_id'),
        'redirect_uri'  => site_url('?auth=casdoor'),
        'state'         => urlencode($redirect),
    );
    
    $backend = casdoor_get_option('backend');
    $backend = rtrim($backend, '/');
    
    return $backend . '/login/oauth/authorize?' . http_build_query($params);
}

/**
 * Get the Casdoor signup URL
 *
 * @param string $application_name Application name to use for signup
 * @return string
 */
function casdoor_get_signup_url($application_name = '') {
    if (empty($application_name)) {
        $application_name = casdoor_get_option('application');
        if (empty($application_name)) {
            $application_name = 'app-built-in';
        }
    }
    
    $backend = casdoor_get_option('backend');
    $backend = rtrim($backend, '/');
    
    return $backend . '/signup/' . urlencode($application_name);
}

/**
 * Check if a URL is same-origin (relative or same host)
 *
 * @param string $url URL to check
 * @return bool
 */
function casdoor_same_origin($url) {
    if ($url === '') {
        return false;
    }
    
    $parsed = wp_parse_url($url);
    
    // Relative URLs are same-origin
    if (empty($parsed['host'])) {
        return true;
    }
    
    $site = wp_parse_url(home_url());
    if (empty($site['host'])) {
        return false;
    }
    
    return strtolower($parsed['host']) === strtolower($site['host']);
}

/**
 * Resolve nested redirect chains (redirect_to, redirect, wc-redirect, return_to)
 *
 * @param string $url URL to resolve
 * @return string
 */
function casdoor_resolve_redirect_chain($url) {
    $current = $url;
    $keys = array('redirect_to', 'redirect', 'wc-redirect', 'return_to');
    
    for ($i = 0; $i < 3; $i++) {
        if ($current === '') {
            break;
        }
        
        $parsed = wp_parse_url($current);
        if (empty($parsed['query'])) {
            break;
        }
        
        parse_str($parsed['query'], $query_params);
        $candidate = '';
        
        foreach ($keys as $key) {
            if (!empty($query_params[$key])) {
                $candidate = (string) $query_params[$key];
                break;
            }
        }
        
        if ($candidate === '' || !casdoor_same_origin($candidate)) {
            break;
        }
        
        $current = $candidate;
    }
    
    return $current;
}

/**
 * Get the intended login redirect target from the current request
 *
 * @return string
 */
function casdoor_get_login_target_from_request() {
    $target = '';
    
    // Check redirect_to parameter
    if (!empty($_REQUEST['redirect_to'])) {
        $resolved = casdoor_resolve_redirect_chain((string) $_REQUEST['redirect_to']);
        if ($resolved !== '' && casdoor_same_origin($resolved)) {
            $target = wp_sanitize_redirect($resolved);
        }
    }
    
    // Fallback to referer
    if ($target === '') {
        $referer = wp_get_referer();
        if (!empty($referer)
            && casdoor_same_origin($referer)
            && strpos($referer, 'wp-login.php') === false
            && strpos($referer, '?auth=casdoor') === false) {
            $target = wp_sanitize_redirect($referer);
        }
    }
    
    // Final fallback to admin or home
    if ($target === '') {
        $fallback = admin_url();
        if (!casdoor_same_origin($fallback)) {
            $fallback = home_url('/');
        }
        $target = wp_sanitize_redirect($fallback);
    }
    
    return $target;
}

/**
 * Login button shortcode
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function casdoor_login_button_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'type'   => 'primary',
            'title'  => 'Login using Casdoor',
            'class'  => 'sso-button',
            'target' => '_blank',
            'text'   => 'Casdoor Single Sign-On',
        ),
        $atts,
        'casdoor_sso_button'
    );
    
    $url = site_url('?auth=casdoor');
    
    return sprintf(
        '<a class="%s" href="%s" title="%s" target="%s">%s</a>',
        esc_attr($atts['class']),
        esc_url($url),
        esc_attr($atts['title']),
        esc_attr($atts['target']),
        esc_html($atts['text'])
    );
}
add_shortcode('casdoor_sso_button', 'casdoor_login_button_shortcode');
