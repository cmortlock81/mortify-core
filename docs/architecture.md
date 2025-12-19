Mortify Core — Architecture Overview
Architectural Intent

Mortify Core is an app shell, not a theme.

It behaves like a container that:

Intercepts requests to /app

Renders content using Mortify templates

Leaves the rest of WordPress untouched

High-Level Flow
Browser
  ↓
/app/* request
  ↓
Mortify Router
  ↓
App Shell Template
  ↓
Content Resolver
  ↓
WordPress / WooCommerce

Directory Responsibilities
mortify-core/
├── mortify2026.php        ← Plugin bootstrap (to be renamed later)
├── includes/
│   ├── class-router.php   ← Handles /app routing
│   ├── class-admin.php    ← Admin menu & settings
│   ├── class-woocommerce.php ← Woo bridge (to be extracted later)
│   ├── class-pwa.php      ← PWA hooks (legacy, optional)
│   └── helpers.php        ← Shared utility functions
├── templates/
│   ├── mortify-app.php    ← App shell layout
│   ├── store-grid.php     ← Woo shop
│   ├── product.php
│   ├── cart.php
│   └── checkout.php
├── assets/
│   ├── css/app.css
│   ├── js/app.js
│   └── js/mortify-nav.js

Routing Strategy

Mortify owns only /app

Child routes resolve internally:

/app/home

/app/shop

/app/cart

No WordPress rewrite rules are applied outside /app

This guarantees safety and reversibility.

Extension Strategy

Mortify Core exposes hooks that allow other plugins to:

Register navigation items

Inject routes

Provide templates

Extend admin settings

This enables:

mortify-woocommerce

mortify-pwa

Future add-ons

Why This Architecture Was Chosen

Previous generations attempted:

Theme overrides

FSE interception

Global template replacement

Those approaches proved fragile.

The /app isolation strategy is:

Safer

Predictable

Easier to debug

Compatible with any theme

Stability Rules (Non-Negotiable)

No code in Core may affect non-/app pages

No forced page creation outside /app

No silent activation side effects

No global filters without scope checks

Summary

Mortify Core is intentionally minimal.

Its job is to:

Provide a stable application container that WordPress can live inside — safely.

Everything else is an extension.
