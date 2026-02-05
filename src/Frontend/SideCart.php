<?php

namespace Himuon\Flex\Cart\Frontend;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}


final class SideCart
{
    public function register()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_footer', [$this, 'content'], 100);
        add_filter('woocommerce_add_to_cart_fragments', [$this, 'addFragments']);

        add_action('wp_ajax_himuon_update_cart_item', [$this, 'updateCartItem']);
        add_action('wp_ajax_nopriv_himuon_update_cart_item', [$this, 'updateCartItem']);
        add_action('wc_ajax_himuon_update_cart_item', [$this, 'updateCartItem']);


        add_action('wp_ajax_himuon_render_variation', [$this, 'renderVariation']);
        add_action('wp_ajax_nopriv_himuon_render_variation', [$this, 'renderVariation']);
        add_action('wc_ajax_himuon_render_variation', [$this, 'renderVariation']);
    }
    public function enqueueScripts()
    {
        wp_enqueue_style(
            'himuon-flex-cart',
            HIMUON_FLEX_CART_URL . 'assets/css/himuon-flex-cart.css',
            [],
            HIMUON_FLEX_CART_VERSION
        );

        wp_enqueue_script(
            'himuon-flex-cart',
            HIMUON_FLEX_CART_URL . 'assets/js/himuon-flex-cart.js',
            ['jquery', 'wc-cart-fragments'],
            HIMUON_FLEX_CART_VERSION,
            true
        );

        wp_enqueue_script('wc-add-to-cart-variation');

        wp_localize_script('himuon-flex-cart', 'himuonFlexCart', [
            'nonce' => wp_create_nonce('himuon_flex_cart'),
            'url' => admin_url('admin-ajax.php')
        ]);
    }

    public function content()
    {
        if (!function_exists('WC') || !WC()->cart)
            return '';

        $items = WC()->cart->get_cart();
        ob_start();
        require_once HIMUON_FLEX_CART_PATH . 'templates/side-cart.php';
        echo ob_get_clean();
    }

    public function addFragments($fragments)
    {
        ob_start();
        $items = WC()->cart ? WC()->cart->get_cart() : [];
        require HIMUON_FLEX_CART_PATH . 'templates/side-cart.php';
        $fragments['#himuon-side-cart'] = ob_get_clean();

        return $fragments;
    }

    public function updateCartItem()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => 'Cart not available.'], 400);
        }

        $cartItemKey = isset($_POST['cartItemKey']) ? wc_clean(wp_unslash($_POST['cartItemKey'])) : '';
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

        if ('' === $cartItemKey || $quantity < 0) {
            wp_send_json_error(['message' => 'Invalid cart data.'], 400);
        }

        WC()->cart->set_quantity($cartItemKey, $quantity, true);
        WC()->cart->calculate_totals();

        $fragments = apply_filters('woocommerce_add_to_cart_fragments', []);
        $cartHash = WC()->cart->get_cart_hash();

        wp_send_json_success([
            'fragments' => $fragments,
            'cart_hash' => $cartHash,
        ]);
    }

    public function renderVariation()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        if (!function_exists('WC')) {
            wp_send_json_error(['message' => 'WooCommerce not available.'], 400);
        }

        // Parent product ID
        $productID = isset($_POST['productId']) ? absint(wc_clean(wp_unslash($_POST['productId']))) : '';
        $product = wc_get_product($productID);

        if (!$product || !$product->is_type('variable')) {
            wp_send_json_error(['message' => 'Invalid Product.'], 400);
        }

        $previousProduct = null;
        if (isset($GLOBALS['product'])) {
            $previousProduct = $GLOBALS['product'];
        }

        $GLOBALS['product'] = $product;

        remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);

        ob_start();
        echo '<div>' . $product->get_name() . '</div>';
        $attributes = $product->get_variation_attributes();
        $availableVariations = $product->get_available_variations();

        wc_get_template(
            'single-product/add-to-cart/variable.php',
            [
                'available_variations' => $availableVariations,
                'attributes' => $attributes,
            ]
        );

        echo '
        <div class="himuon-cart--update-variation-actions">
            <a href="">View Product</a>
            <button type="submit">Update</button>
        </div>';

        $output = ob_get_clean();
        if (null !== $previousProduct) {
            $GLOBALS['product'] = $previousProduct;
        } else {
            unset($GLOBALS['product']);
        }

        add_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);

        wp_send_json_success(['html' => $output]);
    }

}
