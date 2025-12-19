<?php
/**
 * Plugin Name: Mortify 2026
 * Description: App-style mobile shell for WordPress with PWA, WooCommerce integration, and modern Tailwind-inspired UI.
 * Version: 1.5.6
 * Author: Chris Mortlock
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Text Domain: mortify2026
 */

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
require_once MORTIFY2026_PATH . 'includes/class-woocommerce.php';

/**
 * Main plugin class.
 */
class Mortify2026 {

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
        new Mortify2026_Router();
        new Mortify2026_PWA();
        new Mortify2026_Admin();

        // Load WooCommerce support if WC is active
        if ( class_exists( 'WooCommerce' ) ) {
            new Mortify2026_WooCommerce();
        }
    }

    /**
     * On activation: create the /app/ page and flush rewrite rules.
     *
     * @return void
     */
    public static function activate(): void {
        Mortify2026_Router::activate();
        Mortify2026_PWA::activate();
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

/**
 * Register activation/deactivation hooks
 */
register_activation_hook( __FILE__, [ 'Mortify2026', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Mortify2026', 'deactivate' ] );

/**
 * Initialize plugin
 */
new Mortify2026();

// Force WooCommerce URLs to stay inside Mortify app routes.
add_filter( 'woocommerce_get_cart_url', function( $url ) {
    return site_url( '/app/cart' );
}, 99 );

add_filter( 'woocommerce_get_checkout_url', function( $url ) {
    return site_url( '/app/checkout' );
}, 99 );

// Prevent WordPress/Woo canonical redirects from escaping /app routes.
add_filter( 'redirect_canonical', function( $redirect, $requested ) {
    $path = parse_url( $requested ?: ( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
    if ( is_string( $path ) && strpos( $path, '/app' ) === 0 ) {
        return false;
    }
    return $redirect;
}, 99, 2 );




/**
 * Register Mortify app virtual routes under /app/* so reload/share works.
 */
function mortify2026_register_app_routes() {
    // product: /app/product/{slug}
    add_rewrite_tag( '%mortify_product%', '([^&]+)' );
    add_rewrite_tag( '%mortify_view%', '([^&]+)' );

    add_rewrite_rule(
        '^app/product/([^/]+)/?$',
        'index.php?pagename=app&mortify_view=product&mortify_product=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^app/cart/?$',
        'index.php?pagename=app&mortify_view=cart',
        'top'
    );

    add_rewrite_rule(
        '^app/checkout/?$',
        'index.php?pagename=app&mortify_view=checkout',
        'top'
    );
}
add_action( 'init', 'mortify2026_register_app_routes', 1 );

// Ensure rewrites are flushed on activation.
register_activation_hook( __FILE__, function() {
    mortify2026_register_app_routes();
    flush_rewrite_rules();
} );



function mortify2026_is_app_request() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return strpos( $uri, '/app' ) === 0;
}


// Scoped Woo overrides: ONLY inside /app/*
add_filter( 'woocommerce_get_cart_url', function( $url ) {
    if ( mortify2026_is_app_request() ) {
        return site_url( '/app/cart' );
    }
    return $url;
}, 99 );

add_filter( 'woocommerce_get_checkout_url', function( $url ) {
    if ( mortify2026_is_app_request() ) {
        return site_url( '/app/checkout' );
    }
    return $url;
}, 99 );

add_filter( 'redirect_canonical', function( $redirect, $requested ) {
    if ( mortify2026_is_app_request() ) {
        return false;
    }
    return $redirect;
}, 99, 2 );


// Mortify cart count endpoint (scoped, lightweight)
add_action('wp_ajax_mortify_cart_count', 'mortify2026_cart_count');
add_action('wp_ajax_nopriv_mortify_cart_count', 'mortify2026_cart_count');
function mortify2026_cart_count() {
    if ( ! class_exists('WooCommerce') || ! function_exists('WC') ) {
        wp_send_json(['count' => 0]);
    }
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    wp_send_json(['count' => intval($count)]);
}


/**
 * Ensure Woo session is initialized and handle add-to-cart for Mortify app requests.
 * This must run before template output so cart persists across requests.
 */
function mortify2026_handle_app_add_to_cart() {
    if ( ! mortify2026_is_app_request() ) return;
    if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) ) return;

    // Ensure session + cart are available and persist.
    if ( WC()->session ) {
        WC()->session->set_customer_session_cookie( true );
    }
    if ( ! WC()->cart ) {
        wc_load_cart();
    }

    // Handle add-to-cart for simple/variable products.
    if ( isset( $_POST['add-to-cart'] ) ) {
        $product_id   = absint( $_POST['add-to-cart'] );
        $quantity     = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;

        $variations = [];
        foreach ( $_POST as $key => $value ) {
            if ( strpos( $key, 'attribute_' ) === 0 ) {
                $variations[ wc_clean( $key ) ] = wc_clean( wp_unslash( $value ) );
            }
        }

        if ( $product_id > 0 ) {
    $p = wc_get_product( $product_id );
    if ( $p && $p->is_type( 'variable' ) ) {
        // Reject invalid variable add-to-cart without a variation_id.
        if ( empty( $variation_id ) ) {
            return;
        }
    }

            WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );
            WC()->cart->calculate_totals();
        }
    }
}
add_action( 'wp_loaded', 'mortify2026_handle_app_add_to_cart', 5 );


/**
 * AJAX: Update cart quantities and remove items for Mortify app.
 */
function mortify2026_ajax_update_cart() {
    if ( ! class_exists('WooCommerce') || ! function_exists('WC') ) {
        wp_send_json_error();
    }
    if ( ! WC()->cart ) {
        wc_load_cart();
    }

    $key = isset($_POST['key']) ? wc_clean( wp_unslash($_POST['key']) ) : '';
    $act = isset($_POST['act']) ? wc_clean( wp_unslash($_POST['act']) ) : '';

    if ( ! $key || ! $act ) {
        wp_send_json_error();
    }

    $item = WC()->cart->get_cart_item( $key );
    if ( ! $item && $act !== 'remove' ) {
        wp_send_json_error();
    }

    if ( $act === 'remove' ) {
        WC()->cart->remove_cart_item( $key );
        WC()->cart->calculate_totals();
        $empty = WC()->cart->is_empty();
        wp_send_json_success([
            'removed' => true,
            'subtotal' => html_entity_decode( wp_strip_all_tags( wc_price( WC()->cart->get_subtotal() ) ), ENT_QUOTES, get_bloginfo('charset') ),
            'cart_empty' => $empty,
        ]);
    }

    $qty = intval( $item['quantity'] );
    if ( $act === 'inc' ) { $qty++; }
    if ( $act === 'dec' ) {
        if ( $qty <= 1 ) {
            WC()->cart->remove_cart_item( $key );
            WC()->cart->calculate_totals();
            $empty = WC()->cart->is_empty();
            wp_send_json_success([
                'removed' => true,
                'subtotal' => html_entity_decode( wp_strip_all_tags( wc_price( WC()->cart->get_subtotal() ) ), ENT_QUOTES, get_bloginfo('charset') ),
                'cart_empty' => $empty,
            ]);
        }
        $qty = $qty - 1;
    }

    WC()->cart->set_quantity( $key, $qty, true );
    WC()->cart->calculate_totals();

    $updated = WC()->cart->get_cart_item( $key );

    wp_send_json_success([
        'item' => [
            'qty' => intval( $updated['quantity'] ),
            'line_total' => html_entity_decode( wp_strip_all_tags( wc_price( $updated['line_total'] ) ), ENT_QUOTES, get_bloginfo('charset') ),
        ],
        'subtotal' => html_entity_decode( wp_strip_all_tags( wc_price( WC()->cart->get_subtotal() ) ), ENT_QUOTES, get_bloginfo('charset') ),
        'cart_empty' => WC()->cart->is_empty(),
    ]);
}
add_action('wp_ajax_mortify_update_cart', 'mortify2026_ajax_update_cart');
add_action('wp_ajax_nopriv_mortify_update_cart', 'mortify2026_ajax_update_cart');
