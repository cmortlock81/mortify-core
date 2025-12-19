/**
 * Mortify 2026 App JS
 * Handles PWA registration, dynamic navigation,
 * cart count sync, and SPA-like page transitions.
 */

(function () {
	"use strict";

	// --- Constants
	const main = document.getElementById("mortify-main");
	const titleEl = document.getElementById("mortify-page-title");
	const backBtn = document.getElementById("mortify-back");
	const cartCount = document.getElementById("mortify-cart-count");

	// --- Service Worker Registration (backup to inline script)
	if ("serviceWorker" in navigator) {
		navigator.serviceWorker
			.register(mortifyApp.app_base + "sw.js")
			.catch((err) => console.warn("SW registration failed:", err));
	}

	// --- AJAX Navigation (SPA-like)
	document.addEventListener("click", async (e) => {
		const link = e.target.closest("a");
		if (!link) return;

		// Only intercept internal app links
		if (
			link.origin === window.location.origin &&
			link.pathname.startsWith("/" + mortifyApp.app_slug) &&
			!link.hasAttribute("data-noajax")
		) {
			e.preventDefault();
			await loadPage(link.href);
			window.history.pushState({}, "", link.href);
			scrollTo({ top: 0, behavior: "smooth" });
		}
	});

	// --- Handle Back/Forward browser navigation
	window.addEventListener("popstate", () => loadPage(location.href));

	/**
	 * Load a page via AJAX and replace main content
	 */
	async function loadPage(url) {
		main.classList.add("opacity-50");

		try {
			const res = await fetch(url, { credentials: "same-origin" });
			if (!res.ok) throw new Error("HTTP " + res.status);
			const text = await res.text();

			// Parse returned HTML
			const parser = new DOMParser();
			const doc = parser.parseFromString(text, "text/html");

			// Extract main content
			const newMain = doc.querySelector("#mortify-main");
			if (newMain) {
				main.innerHTML = newMain.innerHTML;
			}

			// Update title
			const newTitle = doc.querySelector("title");
			if (newTitle) {
				document.title = newTitle.textContent;
				titleEl.textContent = newTitle.textContent;
			}

			// Hide back button on /app/ home
			const path = new URL(url).pathname.replace(/\/$/, "");
			if (path === "/" + mortifyApp.app_slug || path === "/" + mortifyApp.app_slug + "/") backBtn.classList.add("hidden");
			else backBtn.classList.remove("hidden");

			// Re-highlight footer tabs
			highlightActiveTab(url);
		} catch (err) {
			console.warn("Mortify AJAX navigation failed:", err);
			window.location.href = url; // fallback
		} finally {
			main.classList.remove("opacity-50");
		}
	}

	/**
	 * Highlight active footer tab
	 */
	function highlightActiveTab(url) {
		const tabs = document.querySelectorAll("#mortify-footer-tabs a");
		tabs.forEach((tab) => {
			if (url.startsWith(tab.href)) {
				tab.classList.add("text-blue-600", "font-semibold");
			} else {
				tab.classList.remove("text-blue-600", "font-semibold");
			}
		});
	}

	/**
	 * Periodic Cart Count Update
	 */
	async function updateCartCount() {
		if (!cartCount) return;
		try {
			const res = await fetch(mortifyApp.ajax_url + "?action=mortify_get_cart_count");
			const data = await res.json();
			if (data.success && data.data.count !== undefined) {
				cartCount.textContent = data.data.count;
				cartCount.style.display = data.data.count > 0 ? "inline-block" : "none";
			}
		} catch (e) {
			console.warn("Cart count fetch failed:", e);
		}
	}
	updateCartCount();
	setInterval(updateCartCount, 15000);
})();
