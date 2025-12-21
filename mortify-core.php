<?php
/**
 * Plugin Name: Mortify Core
 * Plugin URI: https://mortify.co.uk
 * Description: App shell and routing engine for WordPress.
 * Version: 1.5.6
 * Requires PHP: 8.1
 * Requires at least: 6.4
 * Author: Chris Mortlock
 * Text Domain: mortify2026
 */

namespace {
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    /**
     * Define core plugin constants
     */
    define( 'MORTIFY2026_PATH', plugin_dir_path( __FILE__ ) );
    define( 'MORTIFY2026_URL', plugin_dir_url( __FILE__ ) );
    define( 'MORTIFY2026_VERSION', '1.5.6' );

    /**
     * Load core includes
     */
    require_once MORTIFY2026_PATH . 'includes/helpers.php';
    require_once MORTIFY2026_PATH . 'includes/class-router.php';
    require_once MORTIFY2026_PATH . 'includes/class-pwa.php';
    require_once MORTIFY2026_PATH . 'includes/class-admin.php';
    // Optional integrations live in separate plugins (e.g., Mortify WooCommerce).
}

namespace Mortify\Core {

/**
 * Main plugin class.
 */
class Plugin {

    /**
     * Initialize hooks and classes.
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'boot' ] );
    }

    /**
     * Bootstraps the plugin modules after all plugins are loaded.
     *
     * @return void
     */
    public function boot(): void {
        new Router();
        new PWA();
        new Admin();
    }

    /**
     * On activation: create the /app/ page and flush rewrite rules.
     *
     * @return void
     */
    public static function activate(): void {
        Router::activate();
        PWA::activate();
        flush_rewrite_rules();
    }

    /**
     * On deactivation: flush rewrite rules.
     *
     * @return void
     */
    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}

class_alias( Plugin::class, 'Mortify2026' );
}

namespace {
    use Mortify\Core\Plugin;

    /**
     * Register activation/deactivation hooks
     */
    register_activation_hook( __FILE__, [ Plugin::class, 'activate' ] );
    register_deactivation_hook( __FILE__, [ Plugin::class, 'deactivate' ] );

    /**
     * Initialize plugin
     */
    new Plugin();

    /**
     * Show a gentle nudge when WooCommerce is active but the Mortify add-on is missing.
     */
    add_action( 'admin_notices', function() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        if ( defined( 'MORTIFY_WOOCOMMERCE_ACTIVE' ) ) {
            return;
        }

        echo '<div class="notice notice-info"><p>' . esc_html__( 'WooCommerce detected. Install the Mortify WooCommerce add-on to enable shop, cart, and checkout inside the Mortify app.', 'mortify2026' ) . '</p></div>';
    } );
}
