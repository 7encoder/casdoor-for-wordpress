<?php
/**
 * URL Rewrites and Request Handling
 *
 * @package CasdoorWordPress
 */

// Prevent direct access
defined('ABSPATH') || exit;

/**
 * Class Casdoor_Rewrites
 * Handles URL rewrites, redirects, and WooCommerce integration
 */
class Casdoor_Rewrites {
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_filter('rewrite_rules_array', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('wp_loaded', array($this, 'flush_rules_maybe'));
        add_action('template_redirect', array($this, 'handle_template_redirect'));
        add_action('wp_footer', array($this, 'woocommerce_footer_script'), 99);
        add_filter('allowed_redirect_hosts', array($this, 'add_allowed_redirect_hosts'));
        add_action('wp_enqueue_scripts', array($this, 'woocommerce_checkout_script'));
    }

    /**
     * Add custom rewrite rules
     *
     * @param array $rules Existing rewrite rules
     * @return array Modified rewrite rules
     */
    public function add_rewrite_rules($rules) {
        global $wp_rewrite;
        $new_rule = array(
            'auth/(.+)' => 'index.php?auth=' . $wp_rewrite->preg_index(1),
        );
        return $new_rule + $rules;
    }

    /**
     * Add custom query vars
     *
     * @param array $vars Existing query vars
     * @return array Modified query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'auth';
        $vars[] = 'code';
        $vars[] = 'message';
        return $vars;
    }

    /**
     * Flush rewrite rules if needed
     */
    public function flush_rules_maybe() {
        if (get_option('casdoor_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('casdoor_flush_rewrite_rules');
        }
    }

    /**
     * Check if WooCommerce feature is enabled
     *
     * @return bool
     */
    private function is_woocommerce_feature_enabled() {
        return absint(casdoor_get_option('woo_edit_account_redirect')) === 1;
    }

    /**
     * Get Casdoor backend URL
     *
     * @return string
     */
    private function get_casdoor_backend() {
        $backend = (string) casdoor_get_option('backend');
        return trim($backend) === '' ? '' : rtrim(trim($backend), '/');
    }

    /**
     * Handle template redirects
     */
    public function handle_template_redirect() {
        // WooCommerce: Server-side redirect for /my-account/edit-account and /account/edit-account
        if ($this->is_woocommerce_feature_enabled()) {
            $backend = $this->get_casdoor_backend();
            if ($backend !== '') {
                $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
                $path = parse_url($request_uri, PHP_URL_PATH);
                if ($path) {
                    $path = '/' . ltrim($path, '/');
                    if (preg_match('#^/(?:my-account|account)/edit-account/?$#i', $path)) {
                        wp_redirect($backend . '/account', 301);
                        exit;
                    }
                }
            }
        }

        // Protect WooCommerce account pages: redirect to login if not authenticated
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        if (!is_user_logged_in() && $request_uri !== '') {
            $path = parse_url($request_uri, PHP_URL_PATH);
            if ($path) {
                $path = '/' . ltrim($path, '/');
                if (preg_match('#^/my-account(?:/|$)#i', $path)) {
                    $query_string = isset($_SERVER['QUERY_STRING']) ? sanitize_text_field(wp_unslash($_SERVER['QUERY_STRING'])) : '';
                    $request_uri = $path . ($query_string !== '' ? '?' . $query_string : '');
                    $return_to = home_url($request_uri);
                    wp_safe_redirect(wp_login_url($return_to));
                    exit;
                }
            }
        }

        // Handle Casdoor authentication
        $activated = absint(casdoor_get_option('active'));
        if (!$activated) {
            return;
        }

        global $wp_query;
        $auth = $wp_query->get('auth');
        $options = get_option('casdoor_options', array());

        if ($auth !== '') {
            // Handle casdoor callback with code parameter
            $matches = array();
            preg_match('/^([a-zA-Z]+)(\?code=[a-zA-Z0-9]+)?$/', $auth, $matches);
            if (count($matches) === 3 && $matches[1] === 'casdoor') {
                $parts = explode('=', $matches[2]);
                if ($parts[0] === '?code') {
                    $url = home_url('?auth=casdoor&code=' . urlencode($parts[1]));
                    wp_safe_redirect($url);
                    exit;
                }
            }
        }

        // Handle error messages
        global $pagenow;
        $message = $wp_query->get('message');
        if ($pagenow === 'index.php' && isset($message)) {
            $options['auto_sso'] = 0;
            require_once CASDOOR_PLUGIN_DIR . 'templates/error-msg.php';
        }

        // Auto SSO for non-logged-in users
        $auto_sso = isset($options['auto_sso']) && $options['auto_sso'] == 1 && !is_user_logged_in();

        if ($auth === 'casdoor' || $auto_sso) {
            require_once CASDOOR_PLUGIN_DIR . 'includes/callback.php';
            exit;
        }
    }

    /**
     * Inject footer script for WooCommerce link rewriting
     */
    public function woocommerce_footer_script() {
        if (!$this->is_woocommerce_feature_enabled()) {
            return;
        }

        $backend = $this->get_casdoor_backend();
        if ($backend === '') {
            return;
        }

        $data = array(
            'base'   => $backend,
            'origin' => home_url('/'),
        );
        ?>
        <script>
        (function() {
            var DATA = <?php echo wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
            var CASDOOR_BASE = (DATA.base || '').replace(/\/+$/, '');
            var SITE_ORIGIN;
            try {
                SITE_ORIGIN = new URL(DATA.origin).origin;
            } catch (e) {
                SITE_ORIGIN = window.location.origin;
            }

            var pathRules = [
                { re: /^\/my-account\/edit-account\/?$/i, casdoorPath: '/account' },
                { re: /^\/account\/edit-account\/?$/i, casdoorPath: '/account' }
            ];

            function normalizeUrl(href) {
                try { return new URL(href, window.location.origin); }
                catch (e) { return null; }
            }

            function maybeRewriteAndTarget(a) {
                if (!a || !a.getAttribute) return;

                var originalHref = a.getAttribute('href');
                if (!originalHref) return;

                var url = normalizeUrl(originalHref);
                if (!url || url.origin !== SITE_ORIGIN) return;

                var path = url.pathname;
                if (!/\/$/.test(path)) {
                    path = path + '/';
                }

                for (var i = 0; i < pathRules.length; i++) {
                    var rule = pathRules[i];
                    if (rule.re.test(path)) {
                        a.setAttribute('target', '_blank');

                        var rel = (a.getAttribute('rel') || '');
                        var parts = rel.toLowerCase().split(/\s+/).filter(Boolean);
                        var set = {};
                        parts.forEach(function(p){ set[p] = true; });
                        set['noopener'] = true;
                        set['noreferrer'] = true;
                        a.setAttribute('rel', Object.keys(set).join(' '));

                        if (CASDOOR_BASE) {
                            var newHref = CASDOOR_BASE + rule.casdoorPath;
                            if (a.href !== newHref) {
                                a.setAttribute('href', newHref);
                            }
                        }
                        return;
                    }
                }
            }

            function processAll(root) {
                var scope = (root && root.querySelectorAll) ? root : document;
                var links = scope.querySelectorAll('a[href]');
                for (var i = 0; i < links.length; i++) {
                    maybeRewriteAndTarget(links[i]);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() { processAll(document); });
            } else {
                processAll(document);
            }

            try {
                var mo = new MutationObserver(function(mutations) {
                    for (var i = 0; i < mutations.length; i++) {
                        var m = mutations[i];
                        if (m.type === 'childList') {
                            for (var j = 0; j < m.addedNodes.length; j++) {
                                var node = m.addedNodes[j];
                                if (node && node.nodeType === 1) {
                                    if (node.tagName === 'A') {
                                        maybeRewriteAndTarget(node);
                                    } else if (node.querySelectorAll) {
                                        processAll(node);
                                    }
                                }
                            }
                        } else if (m.type === 'attributes' && m.target && m.target.tagName === 'A' && m.attributeName === 'href') {
                            maybeRewriteAndTarget(m.target);
                        }
                    }
                });
                mo.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['href'] });
            } catch (e) {
                // MutationObserver not available
            }
        })();
        </script>
        <?php
    }

    /**
     * Inject "Create an Account" link on WooCommerce Block Checkout
     */
    public function woocommerce_checkout_script() {
        if (!$this->is_woocommerce_feature_enabled()) {
            return;
        }

        if (!function_exists('is_checkout') || !is_checkout() || is_user_logged_in()) {
            return;
        }

        $backend = $this->get_casdoor_backend();
        if ($backend === '') {
            return;
        }

        $application = casdoor_get_option('application');
        if (empty($application)) {
            $application = 'app-built-in';
        }

        $signup_url = $backend . '/signup/' . urlencode($application);
        $create_account_text = __('Create an Account', 'casdoor-wordpress-plugin');
        $manage_text = __('Manage your transactions,', 'casdoor-wordpress-plugin');

        $link_html = '<div id="custom-create-account-before-contact-fields" style="font-weight: bold; text-align: right; margin: 0 0 10px;">';
        $link_html .= esc_html($manage_text) . ' ';
        $link_html .= '<a href="' . esc_url($signup_url) . '" target="_blank">' . esc_html($create_account_text) . '</a>';
        $link_html .= '</div>';

        $js_link_html = wp_json_encode($link_html);

        $script = "
            jQuery(function(\$) {
                var linkHTML = {$js_link_html};
                var \$contactFields = \$('#contact-fields');
                var customIdSelector = '#custom-create-account-before-contact-fields';
                var \$formContainer = \$('form.wc-block-checkout__form');

                function injectCreateAccountLink() {
                    if (\$contactFields.length && ! \$(customIdSelector).length) {
                        \$contactFields.before(linkHTML);
                    }
                }

                injectCreateAccountLink();

                if (\$formContainer.length) {
                    var observer = new MutationObserver(function(mutationsList, observer) {
                        injectCreateAccountLink();
                    });

                    observer.observe(\$formContainer[0], { childList: true, subtree: true });
                }
            });
        ";

        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', $script, 'after');
    }

    /**
     * Add Casdoor host to allowed redirect hosts
     *
     * @param array $hosts Existing allowed hosts
     * @return array Modified allowed hosts
     */
    public function add_allowed_redirect_hosts($hosts) {
        // Always add Casdoor backend to allowed hosts when plugin is active
        // This is needed for login redirects to work, not just for WooCommerce features
        $activated = absint(casdoor_get_option('active'));
        if (!$activated) {
            return $hosts;
        }

        $backend = $this->get_casdoor_backend();
        if ($backend !== '') {
            $host = parse_url($backend, PHP_URL_HOST);
            if ($host && !in_array($host, $hosts, true)) {
                $hosts[] = $host;
            }
        }

        return $hosts;
    }
}

// Initialize the rewrites class
new Casdoor_Rewrites();
