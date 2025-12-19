<?php
/**
 * Mortify Core Router
 *
 * Handles routing, template loading, and asset enqueueing
 * for the Mortify App shell under /app/.
 *
 * @package Mortify\Core
 */

namespace Mortify\Core;

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

class Router {

	/**
	 * Constructor — hook into WP lifecycle.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_routes' ] );
		add_filter( 'template_include', [ $this, 'load_app_template' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Register the /app/ rewrite endpoint.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$settings = mortify_get_settings();
		$slug     = trim( $settings['app_slug'], '/' );

		add_rewrite_rule(
			"^{$slug}/?$",
			'index.php?mortify_app=1',
			'top'
		);

		add_rewrite_tag( '%mortify_app%', '1' );
	}

	/**
	 * Load the Mortify app template when /app/ is requested.
	 *
	 * @param string $template The default template.
	 * @return string
	 */
	public function load_app_template( string $template ): string {
		// Force Mortify shell for any request in app scope (e.g. /app and /app/*).
		// This avoids relying on rewrite query vars which can be bypassed by real WP pages.
		if ( mortify_in_app_scope() || get_query_var( 'mortify_app' ) ) {
                        return MORTIFY2026_PATH . 'templates/mortify-app.php';
                }
                return $template;
        }

	/**
	 * Enqueue CSS and JS assets only on /app/.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		if ( ! mortify_in_app_scope() ) {
			return;
		}

		wp_enqueue_style(
			'mortify2026-app',
			MORTIFY2026_URL . 'assets/css/app.css',
			[],
			MORTIFY2026_VERSION
		);

		wp_enqueue_script(
			'mortify2026-app',
			MORTIFY2026_URL . 'assets/js/app.js',
			[ 'jquery' ],
			MORTIFY2026_VERSION,
			true
		);

		wp_localize_script(
			'mortify2026-app',
			'mortifyApp',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'home_url'  => home_url(),
                'app_slug'  => $slug,
                'app_base'  => home_url( '/' . $slug . '/' ),
                'cart_api'  => esc_url( home_url( '/wp-json/wc/store/cart' ) ),
                'cart_url'  => ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_cart_url' ) ) ? wc_get_cart_url() : '',
			]
		);
	}

	/**
	 * Create /app/ page on activation (if not exists).
	 *
	 * @return void
	 */
	public static function activate(): void {
		$settings = mortify_get_settings();
		$slug     = trim( $settings['app_slug'], '/' );

		// Check if page exists
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			wp_insert_post( [
				'post_title'   => ucfirst( $slug ),
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => 'This is the Mortify 2026 app shell.',
			] );
		}

		// Rewrite flushing is handled by the main plugin activator.
	}
}

class_alias( Router::class, 'Mortify2026_Router' );
