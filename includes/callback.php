<?php
/**
 * OAuth Callback Handler
 *
 * @package CasdoorWordPress
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Default redirect after login
$user_redirect = home_url('/');

// Check for redirect_to parameter
if (!empty($_GET['redirect_to'])) {
    $resolved = esc_url_raw((string) $_GET['redirect_to']);
    $validated = wp_validate_redirect($resolved, $user_redirect);
    if (!empty($validated)) {
        $user_redirect = $validated;
    }
} elseif (!empty($_GET['redirect_uri'])) {
    // Backward compatibility
    $resolved = esc_url_raw((string) $_GET['redirect_uri']);
    $validated = wp_validate_redirect($resolved, $user_redirect);
    if (!empty($validated)) {
        $user_redirect = $validated;
    }
}

// If no code yet, redirect to authorize endpoint
if (!isset($_GET['code'])) {
    $params = array(
        'oauth'         => 'authorize',
        'response_type' => 'code',
        'client_id'     => casdoor_get_option('client_id'),
        'redirect_uri'  => site_url('?auth=casdoor'),
        'state'         => $user_redirect,
    );
    
    $backend = rtrim(casdoor_get_option('backend'), '/');
    $url = $backend . '/login/oauth/authorize?' . http_build_query($params);
    
    wp_safe_redirect($url);
    exit;
}

// Handle callback with authorization code
if (!empty($_GET['code'])) {
    // Validate state parameter to prevent open redirects
    if (!empty($_GET['state'])) {
        $state = (string) $_GET['state'];
        $validated = wp_validate_redirect(esc_url_raw($state), $user_redirect);
        if (!empty($validated)) {
            $user_redirect = $validated;
        }
    }
    
    $code = sanitize_text_field(wp_unslash($_GET['code']));
    $backend = rtrim(casdoor_get_option('backend'), '/');
    $token_endpoint = $backend . '/api/login/oauth/access_token';
    
    // Exchange authorization code for access token
    $response = wp_remote_post($token_endpoint, array(
        'method'      => 'POST',
        'timeout'     => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array(),
        'body'        => array(
            'grant_type'    => 'authorization_code',
            'client_id'     => casdoor_get_option('client_id'),
            'client_secret' => casdoor_get_option('client_secret'),
            'code'          => $code,
            'redirect_uri'  => site_url('?auth=casdoor'),
        ),
        'cookies'     => array(),
        'sslverify'   => true, // Always verify SSL in production
    ));
    
    if (is_wp_error($response)) {
        wp_die(
            esc_html($response->get_error_message()),
            esc_html__('Casdoor Authentication Error', 'casdoor-wordpress-plugin'),
            array('response' => 500)
        );
    }
    
    $tokens = json_decode(wp_remote_retrieve_body($response));
    
    if (isset($tokens->error)) {
        wp_die(
            esc_html($tokens->error_description ?? $tokens->error),
            esc_html__('Casdoor Token Error', 'casdoor-wordpress-plugin'),
            array('response' => 400)
        );
    }
    
    // Access token is a JWT
    $access_token = isset($tokens->access_token) ? (string) $tokens->access_token : '';
    if ($access_token === '') {
        wp_die(
            esc_html__('Missing access token in Casdoor response.', 'casdoor-wordpress-plugin'),
            esc_html__('Casdoor Authentication Error', 'casdoor-wordpress-plugin'),
            array('response' => 400)
        );
    }
    
    // Store access token in secure, HttpOnly cookie for logout
    $cookie_name = 'casdoor_access_token';
    $cookie_domain = parse_url(home_url(), PHP_URL_HOST);
    $cookie_opts = array(
        'expires'  => time() + DAY_IN_SECONDS,
        'path'     => '/',
        'domain'   => $cookie_domain ?: '',
        'secure'   => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax',
    );
    
    if (PHP_VERSION_ID >= 70300) {
        setcookie($cookie_name, $access_token, $cookie_opts);
    } else {
        setcookie(
            $cookie_name,
            $access_token,
            $cookie_opts['expires'],
            $cookie_opts['path'],
            $cookie_opts['domain'],
            $cookie_opts['secure'],
            $cookie_opts['httponly']
        );
    }
    
    // Decode JWT payload to get user info
    $parts = explode('.', $access_token);
    if (count($parts) !== 3) {
        wp_die(
            esc_html__('Invalid token format from Casdoor.', 'casdoor-wordpress-plugin'),
            esc_html__('Casdoor Authentication Error', 'casdoor-wordpress-plugin'),
            array('response' => 400)
        );
    }
    
    $payload = $parts[1];
    $decoded_payload = base64_decode(strtr($payload, '-_', '+/'));
    $user_info = json_decode($decoded_payload);
    
    if (!$user_info) {
        wp_die(
            esc_html__('Invalid token payload from Casdoor.', 'casdoor-wordpress-plugin'),
            esc_html__('Casdoor Authentication Error', 'casdoor-wordpress-plugin'),
            array('response' => 400)
        );
    }
    
    // Try to find existing user
    $user_id = username_exists($user_info->name);
    if (!$user_id && !empty($user_info->email)) {
        $user = get_user_by('email', $user_info->email);
        if ($user) {
            $user_id = $user->ID;
        }
    }
    
    $login_only = absint(casdoor_get_option('login_only'));
    
    // If user doesn't exist and login_only is enabled, deny access
    if (!$user_id && $login_only) {
        wp_die(
            esc_html__('Your Casdoor account is not linked to a WordPress user. Please contact the administrator.', 'casdoor-wordpress-plugin'),
            esc_html__('Access Denied', 'casdoor-wordpress-plugin'),
            array('response' => 403)
        );
    }
    
    // Create new user if doesn't exist
    if (!$user_id) {
        $random_password = wp_generate_password(24, true, true);
        $user_data = array(
            'user_email'   => $user_info->email ?? '',
            'user_login'   => $user_info->name,
            'user_pass'    => $random_password,
            'display_name' => $user_info->displayName ?? $user_info->name,
        );
        
        // Set role based on Casdoor user type
        if (!empty($user_info->isGlobalAdmin) && $user_info->isGlobalAdmin) {
            $user_data['role'] = 'administrator';
        }
        
        $user_id = wp_insert_user($user_data);
        
        if (is_wp_error($user_id)) {
            wp_die(
                esc_html($user_id->get_error_message()),
                esc_html__('User Creation Error', 'casdoor-wordpress-plugin'),
                array('response' => 500)
            );
        }
    }
    
    // Log the user in
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
    
    $user = get_user_by('id', $user_id);
    if ($user) {
        do_action('wp_login', $user->user_login, $user);
    }
    
    // Redirect after successful login
    if (absint(casdoor_get_option('redirect_to_dashboard')) === 1) {
        wp_safe_redirect(admin_url());
    } else {
        wp_safe_redirect($user_redirect);
    }
    exit;
}
