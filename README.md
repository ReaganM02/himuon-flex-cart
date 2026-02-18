![Himuon Flex Cart Thumbnail](http://reagandev.com/wp-content/uploads/2026/02/flex-cart-thumbnail.jpg)
# Himuon Flex Cart

AJAX-powered WooCommerce side cart focused on fast UX and developer-first extensibility.

## Why this plugin

Himuon Flex Cart keeps shoppers on the current page while they:
- update quantities
- edit variations/subscription options
- apply/remove coupons
- review totals and free-shipping progress
- proceed to checkout

All major output areas are extensible via hooks and filters.

## Important

This plugin currently has **no admin settings page** for UI/behavior configuration.
Customization is intentionally done through:
- WordPress hooks/filters
- template overrides/patches

## Core Features

- AJAX side cart with WooCommerce fragment refresh
- In-cart quantity update and item removal
- Variation edit panel (inside side cart)
- Subscription edit support (WCS ATT integration)
- Coupon panel with apply/remove flow
- Optional server-rendered coupon list
- Free-shipping progress bar
- Footer coupon rows, discount, and subtotal/total row
- Mobile-friendly drawer behavior

## Quick Start (Hooks)

### Change cart title

```php
add_filter( 'himuon_flex_cart_header_text', function () {
    return __( 'My Cart', 'your-text-domain' );
} );
```

### Disable free-shipping progress

```php
add_filter( 'himuon_flex_cart_free_shipping_threshold', function () {
    return 0;
} );
```

### Set free-shipping threshold

```php
add_filter( 'himuon_flex_cart_free_shipping_threshold', function () {
    return 75;
} );
```

### Disable coupon list (and list AJAX)

```php
add_filter( 'himuon_flex_cart_enable_coupon_list', '__return_false' );
```

### Override theme colors

```php
add_filter( 'himuon_flex_cart_theme_colors', function ( $colors ) {
    return [
        'primary' => '#16a34a',
        'secondary' => '#09090b',
        'text' => '#52525b',
        'title' => '#27272a',
        'border' => '#e4e4e7',
        'border-light' => '#f4f4f5',
        'danger' => '#ef4444',
        'alt-bg' => '#fafafa',
    ];
} );
```

### Change mini cart icon

```php
add_filter( 'himuon_flex_cart_mini_cart_icon', function () {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 4h10l1 2h3v2h-1l-2 10H6L4 8H3V6h3l1-2zm1.2 4 1.4 8h5l1.4-8H8.2z"/></svg>';
} );
```

### Add custom side cart trigger

Any element with the `himuon-side-cart-handler` class will toggle the side cart.

```html
<button type="button" class="himuon-side-cart-handler">
    Open Cart
</button>
```

### Hide floating mini cart trigger

```php
add_filter( 'himuon_flex_cart_show_mini_cart', '__return_false' );
```

## High-Value Filters

- `himuon_flex_cart_mini_cart_icon` (string SVG) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/Frontend/SideCartView.php#L35))
- `himuon_flex_cart_show_mini_cart` (bool) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/SideCart.php#L18))
- `himuon_flex_cart_enable_coupon_list` (bool) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/Coupon.php#L14))
- `himuon_flex_cart_theme_colors` (array) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/SideCart.php#L94))
- `himuon_flex_cart_free_shipping_threshold` (float) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/Frontend/CartItemView.php#L26))
- `himuon_flex_cart_header_text` (string) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/Frontend/CartItemView.php#L62))
- `himuon_flex_cart_show_thumbnails` (bool) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/Frontend/CartItemView.php#L166))
- `himuon_flex_cart_thumbnail_size` (string) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/Frontend/CartItemView.php#L167))
- `himuon_flex_cart_footer_subtotal_label` (string) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/Frontend/SideCartView.php#L82))
- `himuon_flex_cart_footer_discount_label` (string) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/Frontend/SideCartView.php#L169))
- `himuon_flex_cart_footer_checkout_button_label` (string) ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/src/Frontend/SideCartView.php#L100))

## Footer Action Hooks

- `himuon_flex_cart_footer_start` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L19))
- `himuon_flex_cart_footer_before_coupon_trigger` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L21))
- `himuon_flex_cart_footer_after_coupon_trigger` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L30))
- `himuon_flex_cart_footer_before_applied_coupons` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L32))
- `himuon_flex_cart_footer_coupon_row` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L42))
- `himuon_flex_cart_footer_after_applied_coupons` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L67))
- `himuon_flex_cart_footer_before_discount` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L69))
- `himuon_flex_cart_footer_after_discount` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L80))
- `himuon_flex_cart_footer_before_total` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L82))
- `himuon_flex_cart_footer_after_total` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L93))
- `himuon_flex_cart_footer_before_checkout` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L95))
- `himuon_flex_cart_footer_after_checkout` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L100))
- `himuon_flex_cart_footer_end` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/footer.php#L101))

### Example: Add content before checkout button

```php
add_action( 'himuon_flex_cart_footer_before_checkout', function ( $cart ) {
    if ( ! $cart ) {
        return;
    }

    echo '<div class="my-side-cart-note">';
    echo esc_html__( 'You can update quantities before checkout.', 'your-text-domain' );
    echo '</div>';
}, 10, 1 );
```

## Coupon Panel Action Hooks

- `himuon_flex_cart_coupon_panel_start` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L13))
- `himuon_flex_cart_coupon_before_title` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L19))
- `himuon_flex_cart_coupon_after_title` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L23))
- `himuon_flex_cart_coupon_before_notices` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L25))
- `himuon_flex_cart_coupon_after_notices` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L28))
- `himuon_flex_cart_coupon_before_form` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L30))
- `himuon_flex_cart_coupon_form_start` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L33))
- `himuon_flex_cart_coupon_form_end` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L45))
- `himuon_flex_cart_coupon_after_form` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L47))
- `himuon_flex_cart_coupon_before_list` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L50))
- `himuon_flex_cart_coupon_after_list` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L53))
- `himuon_flex_cart_coupon_before_list_loading` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L55))
- `himuon_flex_cart_coupon_after_list_loading` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L88))
- `himuon_flex_cart_coupon_panel_end` ([source](https://github.com/ReaganM02/himuon-flex-cart/blob/main/templates/coupon.php#L90))

### Example: Add helper text in coupon panel

```php
add_action( 'himuon_flex_cart_coupon_after_form', function () {
    echo '<p class="my-coupon-help">';
    echo esc_html__( 'Tip: coupon codes are case-insensitive.', 'your-text-domain' );
    echo '</p>';
} );
```

## Development Notes

- Main plugin bootstrap: `himuon-flex-cart.php`
- Runtime classes: `src/SideCart.php`, `src/Coupon.php`
- View builders: `src/Frontend/*`
- Templates: `templates/*`
- Frontend scripts:
  - `assets/js/himuon-flex-cart.js`
  - `assets/js/himuon-flex-cart-coupon.js`
  - `assets/js/himuon-flex-cart-add-to-cart.js`

## WordPress.org Metadata

`readme.txt` is kept for WordPress plugin metadata and directory compatibility.
Use this `README.md` for GitHub-focused documentation and developer examples.
