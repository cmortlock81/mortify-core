<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$view       = get_query_var( 'mortify_view' ) ?: 'home';
$view_slug  = get_query_var( 'mortify_slug' ) ?: '';
$template_map = mortify_get_template_map();
$template     = $template_map[ $view ] ?? null;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo esc_html( wp_get_document_title() ); ?></title>
<?php wp_head(); ?>
</head>

<body class="min-h-screen flex flex-col bg-gray-50">
<div id="mortify-app" class="flex flex-col min-h-screen">
    <?php include MORTIFY2026_PATH . 'templates/parts/top-nav.php'; ?>

    <main id="mortify-main" class="flex-1 overflow-y-auto p-4">
        <?php
        if ( $template && file_exists( $template ) ) {
            $mortify_view      = $view;
            $mortify_view_slug = $view_slug;
            include $template;
        } else {
            while ( have_posts() ) {
                the_post();
                the_content();
            }
        }
        ?>
    </main>

    <?php include MORTIFY2026_PATH . 'templates/parts/footer-tabs.php'; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
