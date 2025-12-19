<?php
/**
 * Mortify 2026 Helper Functions
 *
 * Contains utility functions for settings retrieval,
 * app scope detection, and optional developer debugging.
 *
 * @package Mortify2026
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Retrieve merged plugin settings with defaults.
 *
 * @return array
 */
function mortify_get_settings(): array {
    $defaults = [
        'app_slug' => 'app',
        'brand' => [
            'primary' => '#2563eb', // Tailwind blue-600
            'accent'  => '#10b981', // Tailwind emerald-500
            'font'    => 'system-ui',
        ],
        'tabs' => [
            ['label' => 'Home', 'icon' => '🏠', 'url' => home_url('/')],
        ],
        'pwa' => [
            'name'              => 'Mortify 2026',
            'short_name'        => 'Mortify',
            'theme_color'       => '#2563eb',
            'background_color'  => '#ffffff',
            'display'           => 'standalone',
            'start_url'         => '/app/',
        ],
    ];

    $settings = get_option( 'mortify2026_settings', [] );

    return wp_parse_args( $settings, $defaults );
}

/**
 * Determine if the current page is within the /app/ scope.
 *
 * @return bool
 */
function mortify_in_app_scope(): bool {
    $settings = mortify_get_settings();
    $slug     = trim( (string) $settings['app_slug'], '/' );

    $path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $path = (string) parse_url( $path, PHP_URL_PATH );
    $path = '/' . ltrim( $path, '/' );

    // Match both "/{slug}" and "/{slug}/..." (and allow query strings).
    $segments = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
    if ( empty( $segments ) ) {
        return false;
    }

    return ( $segments[0] === $slug );
}

/**
 * Developer debug logger — only active when WP_DEBUG_LOG is true.
 *
 * @param mixed $data Data to log (string|array|object).
 * @return void
 */
function mortify_log( $data ): void {
    if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        $output = ( is_array( $data ) || is_object( $data ) )
            ? print_r( $data, true )
            : $data;

        error_log( '[Mortify2026] ' . $output );
    }
}
