<?php
/**
 * Error Message Template
 *
 * @package CasdoorWordPress
 */

// Prevent direct access
defined('ABSPATH') || exit;

global $wp_query;
$message = $wp_query->get('message');
$alert_message = '';

switch ($message) {
    case 'casdoor_login_only':
        $alert_message = __('This Casdoor account doesn\'t exist in WordPress. Please use another account.', 'casdoor-wordpress-plugin');
        break;
    case 'casdoor_sso_failed':
        $alert_message = __('Casdoor Single Sign-On failed. User mismatch or conflict with existing data.', 'casdoor-wordpress-plugin');
        break;
    case 'casdoor_id_not_allowed':
        $alert_message = __('For security reasons, this user cannot use Single Sign-On.', 'casdoor-wordpress-plugin');
        break;
}

if (!empty($alert_message)) : ?>
    <div class="error">
        <p class="alertbar">
            <?php
            echo esc_html($alert_message);
            echo ' <a href="' . esc_url(site_url('?auth=casdoor')) . '">';
            esc_html_e('Please try again', 'casdoor-wordpress-plugin');
            echo '</a>';
            ?>
        </p>
    </div>
<?php endif;
