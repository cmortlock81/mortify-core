<?php
/**
 * Mortify Core WooCommerce Integration
 *
 * Adds WooCommerce awareness: shop, cart, and account tabs,
 * and provides a live cart count endpoint for the top nav bar.
 *
 * @package Mortify\Core
 */

namespace Mortify\Core;

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

class WooCommerce {

	/**
	 * Hook into WooCommerce lifecycle.
	 */
	public function __construct() {
		add_action( 'wp_ajax_mortify_get_cart_count', [ $this, 'ajax_get_cart_count' ] );
		add_action( 'wp_ajax_nopriv_mortify_get_cart_count', [ $this, 'ajax_get_cart_count' ] );
		add_filter( 'mortify_tabs', [ $this, 'add_wc_tabs' ] );
	}

	/**
	 * Add WooCommerce tabs (Shop, Cart, Account) if WooCommerce is active.
	 *
	 * @param array $tabs Existing tabs from settings.
	 * @return array Modified tabs.
	 */
	public function add_wc_tabs( array $tabs ): array {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return $tabs;
		}

		// Prevent duplication if user already has them.
		$existing_labels = wp_list_pluck( $tabs, 'label' );

		if ( ! in_array( 'Shop', $existing_labels, true ) ) {
			$tabs[] = [
				'label' => __( 'Shop', 'mortify2026' ),
				'icon'  => '🛍️',
				'url'   => wc_get_page_permalink( 'shop' ),
			];
		}

		if ( ! in_array( 'Cart', $existing_labels, true ) ) {
			$tabs[] = [
				'label' => __( 'Cart', 'mortify2026' ),
				'icon'  => '🛒',
				'url'   => wc_get_cart_url(),
			];
		}

		if ( ! in_array( 'Account', $existing_labels, true ) ) {
			$tabs[] = [
				'label' => __( 'Account', 'mortify2026' ),
				'icon'  => '👤',
				'url'   => wc_get_page_permalink( 'myaccount' ),
			];
		}

		return $tabs;
	}

	/**
	 * AJAX endpoint: Return current cart item count.
	 *
	 * @return void
	 */
	public function ajax_get_cart_count(): void {
		if ( ! class_exists( 'WC_Cart' ) ) {
			wp_send_json_error( [ 'count' => 0 ] );
		}

		$cart = WC()->cart;
		if ( ! $cart ) {
			wp_send_json_success( [ 'count' => 0 ] );
		}

                wp_send_json_success( [ 'count' => (int) $cart->get_cart_contents_count() ] );
        }
}

class_alias( WooCommerce::class, 'Mortify2026_WooCommerce' );
