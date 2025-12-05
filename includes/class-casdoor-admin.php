<?php
/**
 * Admin Settings Class
 *
 * @package CasdoorWordPress
 */

// Prevent direct access
defined('ABSPATH') || exit;

/**
 * Class Casdoor_Admin
 * Handles admin settings page
 */
class Casdoor_Admin {
    /**
     * Options name in database
     */
    const OPTIONS_NAME = 'casdoor_options';

    /**
     * Add settings page to admin menu
     */
    public static function add_menu() {
        add_options_page(
            __('Casdoor SSO Settings', 'casdoor-wordpress-plugin'),
            __('Casdoor SSO', 'casdoor-wordpress-plugin'),
            'manage_options',
            'casdoor-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public static function register_settings() {
        register_setting(
            'casdoor_options_group',
            self::OPTIONS_NAME,
            array(__CLASS__, 'validate_settings')
        );
    }

    /**
     * Render the settings page
     */
    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'casdoor-wordpress-plugin'));
        }
        
        wp_enqueue_style('casdoor-admin');
        wp_enqueue_script('casdoor-admin');
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Casdoor SSO Configuration', 'casdoor-wordpress-plugin'); ?></h1>
            
            <p>
                <?php
                printf(
                    /* translators: %1$s: First callback URL, %2$s: Second callback URL */
                    esc_html__('Add the following two callback URLs to your Casdoor application: %1$s and %2$s', 'casdoor-wordpress-plugin'),
                    '<code>' . esc_html(site_url('?auth=casdoor')) . '</code>',
                    '<code>' . esc_html(site_url('/')) . '</code>'
                );
                ?>
            </p>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('casdoor_options_group');
                ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="casdoor_active">
                                    <?php esc_html_e('Activate Casdoor', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="casdoor_active"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[active]" 
                                       value="1" 
                                       <?php checked(absint(casdoor_get_option('active')), 1); ?> />
                                <p class="description">
                                    <?php esc_html_e('Enable Casdoor SSO authentication', 'casdoor-wordpress-plugin'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="casdoor_client_id">
                                    <?php esc_html_e('Client ID', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="casdoor_client_id"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[client_id]" 
                                       value="<?php echo esc_attr(casdoor_get_option('client_id')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="casdoor_client_secret">
                                    <?php esc_html_e('Client Secret', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="casdoor_client_secret"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[client_secret]" 
                                       value="<?php echo esc_attr(casdoor_get_option('client_secret')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="casdoor_backend">
                                    <?php esc_html_e('Backend URL', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="casdoor_backend"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[backend]" 
                                       value="<?php echo esc_attr(casdoor_get_option('backend')); ?>" 
                                       class="regular-text" 
                                       placeholder="https://your-casdoor-domain.com" />
                                <p class="description">
                                    <?php esc_html_e('Example: https://your-casdoor-domain.com', 'casdoor-wordpress-plugin'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="casdoor_organization">
                                    <?php esc_html_e('Organization', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="casdoor_organization"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[organization]" 
                                       value="<?php echo esc_attr(casdoor_get_option('organization')); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Default: built-in', 'casdoor-wordpress-plugin'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="casdoor_application">
                                    <?php esc_html_e('Application', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="casdoor_application"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[application]" 
                                       value="<?php echo esc_attr(casdoor_get_option('application')); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Default: app-built-in', 'casdoor-wordpress-plugin'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="casdoor_redirect_dashboard">
                                    <?php esc_html_e('Redirect to Dashboard', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="casdoor_redirect_dashboard"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[redirect_to_dashboard]" 
                                       value="1" 
                                       <?php checked(absint(casdoor_get_option('redirect_to_dashboard')), 1); ?> />
                                <p class="description">
                                    <?php esc_html_e('Redirect users to the dashboard after signing in', 'casdoor-wordpress-plugin'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="casdoor_login_only">
                                    <?php esc_html_e('Login Only Mode', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="casdoor_login_only"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[login_only]" 
                                       value="1" 
                                       <?php checked(absint(casdoor_get_option('login_only')), 1); ?> />
                                <p class="description">
                                    <?php esc_html_e('Restrict to existing WordPress users only (no automatic user creation)', 'casdoor-wordpress-plugin'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="casdoor_auto_sso">
                                    <?php esc_html_e('Auto SSO', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="casdoor_auto_sso"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[auto_sso]" 
                                       value="1" 
                                       <?php checked(absint(casdoor_get_option('auto_sso')), 1); ?> />
                                <p class="description">
                                    <?php esc_html_e('When enabled, any visitor who is not logged in will be automatically redirected to Casdoor to authenticate. This effectively requires all users to log in before accessing any page on your site.', 'casdoor-wordpress-plugin'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="casdoor_woo_redirect">
                                    <?php esc_html_e('WooCommerce Integration', 'casdoor-wordpress-plugin'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="casdoor_woo_redirect"
                                       name="<?php echo esc_attr(self::OPTIONS_NAME); ?>[woo_edit_account_redirect]" 
                                       value="1" 
                                       <?php checked(absint(casdoor_get_option('woo_edit_account_redirect')), 1); ?> />
                                <p class="description">
                                    <?php esc_html_e('Redirect WooCommerce /my-account/edit-account/ to Casdoor and show signup links on checkout', 'casdoor-wordpress-plugin'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Validate and sanitize settings
     *
     * @param array $input Raw input values
     * @return array Sanitized values
     */
    public static function validate_settings($input) {
        $output = casdoor_defaults();
        
        // Sanitize text fields
        if (isset($input['client_id'])) {
            $output['client_id'] = sanitize_text_field($input['client_id']);
        }
        
        if (isset($input['client_secret'])) {
            $output['client_secret'] = sanitize_text_field($input['client_secret']);
        }
        
        if (isset($input['backend'])) {
            $output['backend'] = esc_url_raw(trim($input['backend']), array('http', 'https'));
        }
        
        if (isset($input['organization'])) {
            $output['organization'] = sanitize_text_field($input['organization']);
        }
        
        if (isset($input['application'])) {
            $output['application'] = sanitize_text_field($input['application']);
        }
        
        // Sanitize checkboxes
        $output['active'] = isset($input['active']) ? 1 : 0;
        $output['redirect_to_dashboard'] = isset($input['redirect_to_dashboard']) ? 1 : 0;
        $output['login_only'] = isset($input['login_only']) ? 1 : 0;
        $output['auto_sso'] = isset($input['auto_sso']) ? 1 : 0;
        $output['woo_edit_account_redirect'] = isset($input['woo_edit_account_redirect']) ? 1 : 0;
        
        return $output;
    }
}
