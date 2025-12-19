<?php
/**
 * Mortify 2026 Footer Tabs
 *
 * Displays bottom tab navigation (Home, Shop, etc.)
 * with active-state highlighting and brand color integration.
 *
 * @package Mortify2026
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = mortify_get_settings();
$tabs     = apply_filters( 'mortify_tabs', $settings['tabs'] );
$brand    = $settings['brand'];
$req_path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
$req_path = (string) parse_url( $req_path, PHP_URL_PATH );
$current  = trailingslashit( home_url( $req_path ) );
?>

<footer id="mortify-footer-tabs"
	class="sticky bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-inner flex justify-around py-2">
	<?php foreach ( $tabs as $tab ) :
		$is_active = str_starts_with( $current, trailingslashit( $tab['url'] ) );
		?>
		<a href="<?php echo esc_url( $tab['url'] ); ?>"
			class="flex flex-col items-center justify-center text-sm font-medium px-2 transition-all duration-150
			<?php echo $is_active ? 'mortify-text-primary scale-110' : 'text-gray-500 hover:text-gray-800'; ?>">
			<span class="text-xl"><?php echo esc_html( $tab['icon'] ); ?></span>
			<span><?php echo esc_html( $tab['label'] ); ?></span>
		</a>
	<?php endforeach; ?>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
	const currentUrl = window.location.href;
	document.querySelectorAll("#mortify-footer-tabs a").forEach(link => {
		if (currentUrl.startsWith(link.href)) {
			link.classList.add("text-blue-600", "font-semibold");
		}
	});
});
</script>
