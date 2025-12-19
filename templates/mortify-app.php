<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Resolve route
$path  = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
$parts = explode( '/', $path );

$screen = $parts[1] ?? 'home';
$view   = $screen;
$slug   = $parts[2] ?? '';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo esc_html( ucfirst( $screen ) ); ?></title>

<style>
html,body{margin:0;height:100%;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial}
body{overflow:hidden;background:#f9fafb}
.top{position:fixed;top:0;left:0;right:0;height:52px;background:#111827;color:#fff;display:flex;align-items:center;padding:0 16px;font-weight:600}
.content{position:fixed;top:52px;bottom:56px;left:0;right:0;overflow:auto;padding:16px}
.tabs{position:fixed;bottom:0;left:0;right:0;height:56px;border-top:1px solid #e5e7eb;background:#fff;display:flex}
.tabs a{flex:1;text-align:center;line-height:56px;text-decoration:none;color:#374151}
.tabs a.active{color:#111827;font-weight:600}
</style>
</head>

<body>
<div class="top"><?php echo esc_html( ucfirst( $screen ) ); ?></div>

<div class="content">
<?php
switch ( $view ) {
    case 'store':
        include __DIR__ . '/store-grid.php';
        break;

    case 'product':
        $_GET['mortify_product'] = $slug;
        include __DIR__ . '/product.php';
        break;

    case 'cart':
        include __DIR__ . '/cart.php';
        break;

    case 'checkout':
        include __DIR__ . '/checkout.php';
        break;

    default:
        while ( have_posts() ) {
            the_post();
            the_content();
        }
}
?>
</div>

<nav class="tabs">
<?php
$tabs = ['home','store','cart','checkout','more'];
foreach ( $tabs as $t ) {
    $active = $t === $screen ? 'active' : '';
    echo '<a class="'.$active.'" href="/app/'.$t.'">'.ucfirst($t).'</a>';
}
?>
</nav>

</body>
</html>
