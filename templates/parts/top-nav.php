<?php
/**
 * Mortify 2026 Top Navigation
 *
 * Displays back navigation, page title, and cart count.
 *
 * @package Mortify2026
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = mortify_get_settings();
$brand    = $settings['brand'];
?>

<header id="mortify-top-nav"
	class="sticky top-0 z-50 flex items-center justify-between px-4 py-3 bg-white border-b border-gray-200 shadow-sm">
	
	<!-- Back Button -->
	<button id="mortify-back"
		class="text-xl font-semibold text-gray-600 hover:text-gray-800 hidden"
		aria-label="<?php esc_attr_e( 'Go Back', 'mortify2026' ); ?>">
		⬅
	</button>

	<!-- Page Title -->
	<h1 id="mortify-page-title" class="flex-1 text-center font-semibold text-gray-800 truncate">
		<?php echo esc_html( wp_get_document_title() ); ?>
	</h1>

	<!-- Cart Icon -->
	<button id="mortify-cart"
		class="text-xl font-semibold text-gray-600 hover:text-gray-800 relative"
		aria-label="<?php esc_attr_e( 'Cart', 'mortify2026' ); ?>">
		🛒
		<span id="mortify-cart-count"
			class="absolute -top-1 -right-2 text-white text-xs font-bold px-1.5 py-0.5 rounded-full mortify-bg-accent">
			0
		</span>
	</button>
</header>

<script>
document.addEventListener("DOMContentLoaded", function() {
	const backBtn = document.getElementById("mortify-back");
	const titleEl = document.getElementById("mortify-page-title");
	const cartCount = document.getElementById("mortify-cart-count");
	const cartBtn = document.getElementById("mortify-cart");

	// 1️⃣ Back button visibility
	const path = window.location.pathname.replace(/\/$/, "");
	if (!path.endsWith("/app")) {
		backBtn.classList.remove("hidden");
		backBtn.addEventListener("click", () => window.history.back());
	}

	// 2️⃣ Dynamic title updates (if JS changes document.title)
	const observer = new MutationObserver(() => {
		titleEl.textContent = document.title;
	});
	observer.observe(document.querySelector("title"), { childList: true });

	// 3️⃣ Fetch cart count every 15s
	async function updateCartCount() {
		try {
			const response = await fetch(mortifyApp.ajax_url + "?action=mortify_get_cart_count");
			const data = await response.json();
			if (data.success && data.data.count !== undefined) {
				cartCount.textContent = data.data.count;
				cartCount.style.display = data.data.count > 0 ? "inline-block" : "none";
			}
		} catch (err) {
			console.warn("Cart count update failed", err);
		}
	}

	updateCartCount();
	setInterval(updateCartCount, 15000);

	// 4️⃣ Click → go to cart
	cartBtn.addEventListener("click", () => {
		if (mortifyApp.cart_url) { window.location.href = mortifyApp.cart_url; } else { window.location.href = mortifyApp.app_base; }
	});
});
</script>
