# Mortify Core

Mortify Core is the foundational WordPress plugin that provides an
app-style shell for websites, rendered under a dedicated `/app` route.

It is designed to wrap WordPress content (with optional commerce add-ons)
inside a consistent, mobile-first UI without modifying themes,
overriding templates globally, or interfering with the main site.

This repository represents the **frozen baseline** of Mortify Core,
extracted from a known working MVP build.

---

## Key Principles

- Mortify **does not replace** your WordPress theme
- Mortify **does not modify** your site homepage
- Mortify renders **only under `/app`**
- Mortify is **extensible**, not monolithic
- Optional features live in **separate plugins**

---

## What Mortify Core Does

- Registers and handles the `/app` route
- Provides an application shell (top bar, navigation, container)
- Renders WordPress content inside the shell
- Manages internal routing and templates
- Exposes hooks for extensions (WooCommerce, PWA, etc.)
- Provides a minimal admin settings interface

---

## What Mortify Core Does NOT Do

- No licensing system
- No Firebase or push notifications
- No service workers or PWA install prompts
- No onboarding wizard (yet)
- No marketing pages or splash screens

Those features belong in **separate add-on plugins**.

---

## Requirements

- WordPress 6.2+
- PHP 8.1+
- WooCommerce (optional, for commerce features)

### WooCommerce add-on

The core plugin no longer ships WooCommerce templates. Activate the companion
`mortify-woocommerce` plugin (lives as a sibling directory to this repository)
alongside WooCommerce to expose `/app/shop`, `/app/cart`, and related routes.

---

## Installation

1. Download the release ZIP from GitHub
2. Upload via WordPress Admin → Plugins → Add New
3. Activate **Mortify Core**
4. Visit `/app` on your site

Mortify will not affect your existing theme or homepage.

---

## Baseline Version

This repository is tagged with:

