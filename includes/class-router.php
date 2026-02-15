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
         * Cached app slug.
         *
         * @var string
         */
        private string $app_slug;

        /**
         * Constructor — hook into WP lifecycle.
         */
        public function __construct() {
                $this->app_slug = mortify_get_app_slug();

                add_action( 'init', [ $this, 'register_routes' ] );
                add_filter( 'template_include', [ $this, 'load_app_template' ] );
                add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        }

        /**
         * Get the current app slug (without slashes).
         */
        public function get_app_slug(): string {
                return $this->app_slug;
        }

        /**
         * Get the full base URL for the Mortify shell.
         */
        public function get_app_base(): string {
                return mortify_get_app_base();
        }

        /**
         * Register the /app/ rewrite endpoint and expose hook for extensions.
         *
         * @return void
         */
        public function register_routes(): void {
                $slug    = $this->app_slug;
                $pattern = preg_quote( $slug, '#' );

                add_rewrite_tag( '%mortify_app%', '1' );
                add_rewrite_tag( '%mortify_view%', '([^&]+)' );
                add_rewrite_tag( '%mortify_slug%', '([^&]+)' );

                add_rewrite_rule(
                        "^{$pattern}/?$",
                        'index.php?pagename=' . $slug . '&mortify_app=1&mortify_view=home',
                        'top'
                );

                add_rewrite_rule(
                        "^{$pattern}/([^/]+)/?$",
                        'index.php?pagename=' . $slug . '&mortify_app=1&mortify_view=$matches[1]',
                        'top'
                );

                add_rewrite_rule(
                        "^{$pattern}/([^/]+)/(.+)?$",
                        'index.php?pagename=' . $slug . '&mortify_app=1&mortify_view=$matches[1]&mortify_slug=$matches[2]',
                        'top'
                );

                /**
                 * Allow add-ons to register additional routes.
                 *
                 * @param Router $router The router instance.
                 */
                do_action( 'mortify_register_routes', $this );
        }

        /**
         * Load the Mortify app template when /app/ is requested.
         *
         * @param string $template The default template.
         * @return string
         */
        public function load_app_template( string $template ): string {
                // Force Mortify shell for any request in app scope (e.g. /app and /app/*).
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

                $data = [
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'home_url' => home_url(),
                        'app_slug' => $this->app_slug,
                        'app_base' => $this->get_app_base(),
                ];

                $data = apply_filters( 'mortify_app_localize_data', $data );

                wp_localize_script(
                        'mortify2026-app',
                        'mortifyApp',
                        $data
                );
        }

        /**
         * Create /app/ page on activation (if not exists).
         *
         * @return void
         */
        public static function activate(): void {
                $slug = mortify_get_app_slug();

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
