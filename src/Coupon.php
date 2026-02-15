<?php

namespace Himuon\Flex\Cart;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Coupon
{
    public function register()
    {

        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);

        add_action('wp_ajax_himuon_cart_add_coupon', [$this, 'addCoupon']);
        add_action('wp_ajax_nopriv_himuon_cart_add_coupon', [$this, 'addCoupon']);
        add_action('wc_ajax_himuon_cart_add_coupon', [$this, 'addCoupon']);

    }

    public function enqueueScripts()
    {
        wp_enqueue_script(
            'himuon-flex-cart-coupon',
            HIMUON_FLEX_CART_URL . 'assets/js/himuon-flex-cart-coupon.js',
            ['himuon-flex-cart', 'wc-cart-fragments'],
            HIMUON_FLEX_CART_VERSION,
            [
                'strategy' => 'defer',
                'in_footer' => true
            ]
        );

        wp_localize_script(
            'himuon-flex-cart-coupon',
            'himuonFlexCartCoupon',
            [
                'nonce' => wp_create_nonce('himuon_flex_cart_coupon'),
                'messages' => [
                    'requestFailed' => __('Unable to apply coupon right now. Please try again.', 'himuon-flex-cart'),
                ],
            ]
        );
    }


    public function addCoupon()
    {
        check_ajax_referer('himuon_flex_cart_coupon', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => __('Cart not available.', 'himuon-flex-cart')], 400);
        }

        $couponCode = isset($_POST['coupon_code']) ? wc_clean(wp_unslash($_POST['coupon_code'])) : '';
        $couponCode = wc_format_coupon_code($couponCode);

        if ('' === $couponCode) {
            wc_add_notice(__('Please enter a coupon code.', 'himuon-flex-cart'), 'error');
        } else {
            WC()->cart->apply_coupon($couponCode);
            WC()->cart->calculate_totals();
        }

        $noticesHtml = wc_print_notices(true);
        wc_clear_notices();

        $fragments = apply_filters('woocommerce_add_to_cart_fragments', []);
        $cartHash = WC()->cart->get_cart_hash();

        wp_send_json_success([
            'notices_html' => $noticesHtml,
            'fragments' => $fragments,
            'cart_hash' => $cartHash,
        ]);
    }
}
