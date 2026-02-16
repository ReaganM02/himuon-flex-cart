=== Himuon Flex Cart ===
Contributors: reaganm02
Tags: woocommerce, side cart, ajax cart, mini cart, cart drawer
Requires at least: 6.9
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modern AJAX-powered WooCommerce side cart focused on fast UX, in-cart editing, and developer-first customization via hooks and filters.

== Description ==

Himuon Flex Cart replaces the default mini-cart flow with a slide-out WooCommerce side cart designed to reduce friction and keep shoppers moving to checkout.

Shoppers can update quantities, remove items, edit variation selections, apply/remove coupons, and review savings without leaving the current page.

The plugin is built with WooCommerce fragment refreshes and extensibility in mind.

= Key Features =

* AJAX side cart with live WooCommerce fragment updates
* Quantity update and cart item removal inside the drawer
* Variation edit panel for variable products
* Subscription edit support for WooCommerce All Products for Subscriptions
* Coupon apply/remove workflow with optional coupon list
* Footer discount and subtotal display with applied coupon rows
* Free shipping progress section
* Mobile-friendly drawer behavior
* Theme and behavior customization through WordPress hooks and filters
* Template-based architecture for maintainable overrides/extensions

= Developer Customization =

This plugin is intentionally developer-oriented.

**There is no admin settings page for UI/behavior customization.**
Use hooks, filters, and template-level customizations to adapt output and behavior.
**More hooks, filters, and code examples are available on GitHub: https://github.com/ReaganM02/himuon-flex-cart**

== Installation ==

1. Upload the `himuon-flex-cart` folder to `/wp-content/plugins/`.
2. Activate **Himuon Flex Cart** in WordPress admin.
3. Ensure WooCommerce is active.
4. Visit your storefront and add an item to cart to verify side-cart behavior.

== Frequently Asked Questions ==

= Does this plugin have an admin settings page? =

No. The plugin is currently configured for hook/filter and template-based customization.


= Does it support subscriptions? =

Yes, for WooCommerce All Products for Subscriptions integration points used by this plugin.

= Can I disable coupon list rendering? =

Yes, via filter:

`add_filter( 'himuon_flex_cart_enable_coupon_list', '__return_false' );`

= Can I disable or customize the free shipping progress amount? =

Yes. Use `himuon_flex_cart_free_shipping_threshold`.

Disable free shipping progress:

`add_filter( 'himuon_flex_cart_free_shipping_threshold', function () { return 0; } );`

Set a custom minimum (example: 75):

`add_filter( 'himuon_flex_cart_free_shipping_threshold', function () { return 75; } );`

= How do I change the cart title text? =

Use the `himuon_flex_cart_header_text` filter:

`add_filter( 'himuon_flex_cart_header_text', function () { return __( 'My Cart', 'your-text-domain' ); } );`

= Can I use my own button or link to open the side cart? =

Yes. Add the `himuon-side-cart-handler` class to any element:

`<button type="button" class="himuon-side-cart-handler">Open Cart</button>`

= Can I hide the floating mini cart trigger? =

Yes. Use this filter:

`add_filter( 'himuon_flex_cart_show_mini_cart', '__return_false' );`


== Changelog ==

= 1.0.0 =
* Initial public release
* AJAX side cart core interactions
* Variation/subscription in-cart editing
* Coupon apply/remove support
* Coupon list endpoint and loading UI
* Developer hooks and filter-based extensibility

== Upgrade Notice ==

= 1.0.0 =
Initial release.
