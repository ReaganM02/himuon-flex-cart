# Repository Guidelines

## Project Structure & Module Organization
- `himuon-flex-cart.php` is the plugin bootstrap (defines constants, loads autoloader).
- `src/` holds PHP classes, organized by namespace. Frontend behavior lives in `src/Frontend/` (`SideCart.php`).
- `templates/` contains PHP view partials (`templates/side-cart.php`, `templates/cart-item.php`). `templates/index.php` is a "silence is golden" placeholder.
- `assets/css/` contains the plugin stylesheet (`assets/css/himuon-flex-cart.css`).
- `assets/js/` contains the frontend behavior (`assets/js/himuon-flex-cart.js`).
- `vendor/` is Composer-managed autoloading and should not be edited manually.

## Build, Test, and Development Commands
This plugin has no build pipeline or test runner checked in.
- If you add new classes, update autoloads with:
  - `composer dump-autoload`
- For local development, activate the plugin in WordPress and verify output in the frontend.

## Runtime Behavior (Hooks & AJAX)
- Bootstrap: `add_action('plugins_loaded', [Init::class, 'load'])` creates `Frontend\SideCart` and registers hooks.
- Frontend hooks in `SideCart::register()`:
  - `wp_enqueue_scripts` enqueues `himuon-flex-cart.css` and `himuon-flex-cart.js`, plus localized nonce `himuonFlexCart.nonce`.
  - `wp_footer` renders `templates/side-cart.php`.
  - `woocommerce_add_to_cart_fragments` re-renders `#himuon-side-cart` for cart fragments.
  - AJAX endpoint `himuon_update_cart_item` is registered via `wp_ajax_*` and `wc_ajax_*`.
- JS (`assets/js/himuon-flex-cart.js`) uses WooCommerce fragment refresh events and calls the AJAX endpoint to update quantities.

## Coding Style & Naming Conventions
- PHP files use `<?php` with namespaces matching folder structure (e.g., `Himuon\\Flex\\Cart\\Frontend`).
- Prefer PSR-4 class naming and file naming (`SideCart.php`, `Init.php`).
- Indentation uses 4 spaces.
- Keep WordPress standards in mind: use `add_action`, `add_filter`, and escape output (`esc_html`, `wp_kses_post`) in templates.
- Variables and methods must be camelCase.
- CSS classes use the `himuon-cart--*` prefix (e.g., `himuon-cart--header`).

## Testing Guidelines
- No automated tests are present in this repository.
- If you add tests, document how to run them and keep test files under a `tests/` directory.
- For now, validate manually: add to cart, update quantity, and confirm the side cart updates.

## Commit & Pull Request Guidelines
- This repository is not a Git repo here, so no commit history or conventions are available.
- Recommended: use clear, imperative commit messages (e.g., `Add side cart subtotal update`).
- For PRs, include a short description, steps to test (WooCommerce cart flow), and screenshots if UI changes.

## Configuration & Dependencies
- Requires WooCommerce and WordPress (see plugin header in `himuon-flex-cart.php`).
- Keep dependencies minimal; any new PHP packages should be added via Composer.
- Free shipping progress uses the `himuon_flex_cart_free_shipping_threshold` filter; return `0` to hide the progress bar.
  - Example: set `75.00` to show progress toward free shipping at $75.
