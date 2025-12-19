<?php

namespace Mortify\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PWA {

    public function __construct() {
        add_action( 'init', [ $this, 'register_routes' ] );
        add_action( 'template_redirect', [ $this, 'handle_requests' ] );
    }

    public static function activate() {
        add_rewrite_tag( '%mortify2026_manifest%', '1' );
        add_rewrite_tag( '%mortify2026_sw%', '1' );

        add_rewrite_rule(
            '^app/manifest\.webmanifest$',
            'index.php?mortify2026_manifest=1',
            'top'
        );

        add_rewrite_rule(
            '^app/sw\.js$',
            'index.php?mortify2026_sw=1',
            'top'
        );

        flush_rewrite_rules();
    }

    public function register_routes() {
        add_rewrite_tag( '%mortify2026_manifest%', '1' );
        add_rewrite_tag( '%mortify2026_sw%', '1' );

        add_rewrite_rule(
            '^app/manifest\.webmanifest$',
            'index.php?mortify2026_manifest=1',
            'top'
        );

        add_rewrite_rule(
            '^app/sw\.js$',
            'index.php?mortify2026_sw=1',
            'top'
        );
    }

    public function handle_requests() {
        if ( get_query_var( 'mortify2026_manifest' ) ) {
            $this->render_manifest();
            exit;
        }

        if ( get_query_var( 'mortify2026_sw' ) ) {
            $this->render_service_worker();
            exit;
        }
    }

    private function render_manifest() {
        header( 'Content-Type: application/manifest+json' );

        echo wp_json_encode( [
            'name'       => 'Mortify 2026',
            'short_name' => 'Mortify',
            'start_url'  => '/app/',
            'display'    => 'standalone',
            'background_color' => '#ffffff',
            'theme_color'       => '#000000',
        ] );
    }

    private function render_service_worker() {
        header( 'Content-Type: application/javascript' );
        ?>
self.addEventListener('install', function () {
    self.skipWaiting();
});
self.addEventListener('fetch', function () {});
<?php
    }
}

class_alias( PWA::class, 'Mortify2026_PWA' );

new PWA();
