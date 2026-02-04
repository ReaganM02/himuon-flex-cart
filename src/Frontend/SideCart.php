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
}
