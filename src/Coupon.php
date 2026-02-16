<?php

namespace Himuon\Flex\Cart;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Coupon
{
    private function isCouponListEnabled(): bool
    {
        return (bool) apply_filters('himuon_flex_cart_enable_coupon_list', true);
    }

    public function register()
    {

        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);

        add_action('wp_ajax_himuon_cart_add_coupon', [$this, 'add']);
        add_action('wp_ajax_nopriv_himuon_cart_add_coupon', [$this, 'add']);
        add_action('wc_ajax_himuon_cart_add_coupon', [$this, 'add']);

        add_action('wp_ajax_himuon_cart_remove_coupon', [$this, 'remove']);
        add_action('wp_ajax_nopriv_himuon_cart_remove_coupon', [$this, 'remove']);
        add_action('wc_ajax_himuon_cart_remove_coupon', [$this, 'remove']);

        if ($this->isCouponListEnabled()) {
            add_action('wp_ajax_himuon_cart_list_coupons', [$this, 'listCoupons']);
            add_action('wp_ajax_nopriv_himuon_cart_list_coupons', [$this, 'listCoupons']);
            add_action('wc_ajax_himuon_cart_list_coupons', [$this, 'listCoupons']);
        }

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
                    'requestFailed' => __('Unable to update coupon right now. Please try again.', 'himuon-flex-cart'),
                    'listFailed' => __('Unable to load coupons right now. Please try again.', 'himuon-flex-cart'),
                ],
                'enableCouponList' => $this->isCouponListEnabled(),
            ]
        );
    }


    public function add()
    {
        check_ajax_referer('himuon_flex_cart_coupon', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => __('Cart not available.', 'himuon-flex-cart')], 400);
        }

        $couponCode = (string) filter_input(INPUT_POST, 'coupon_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $couponCode = wc_clean(wp_unslash($couponCode));
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

    public function remove()
    {
        check_ajax_referer('himuon_flex_cart_coupon', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => __('Cart not available.', 'himuon-flex-cart')], 400);
        }

        $couponCode = (string) filter_input(INPUT_POST, 'coupon_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $couponCode = wc_clean(wp_unslash($couponCode));
        $couponCode = wc_format_coupon_code($couponCode);

        if ('' === $couponCode) {
            wc_add_notice(__('Invalid coupon code.', 'himuon-flex-cart'), 'error');
        } else {
            $appliedCoupons = WC()->cart->get_applied_coupons();
            if (!in_array($couponCode, $appliedCoupons, true)) {
                wc_add_notice(__('Coupon is not applied.', 'himuon-flex-cart'), 'error');
            } else {
                $removed = WC()->cart->remove_coupon($couponCode);
                if ($removed) {
                    wc_add_notice(__('Coupon removed.', 'himuon-flex-cart'), 'success');
                } else {
                    wc_add_notice(__('Unable to remove coupon.', 'himuon-flex-cart'), 'error');
                }
            }
        }

        WC()->cart->calculate_totals();

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

    public function listCoupons()
    {
        check_ajax_referer('himuon_flex_cart_coupon', 'nonce');

        if (!$this->isCouponListEnabled()) {
            wp_send_json_error(['message' => __('Coupon list is disabled.', 'himuon-flex-cart')], 400);
        }

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => __('Cart not available.', 'himuon-flex-cart')], 400);
        }

        $appliedCoupons = WC()->cart->get_applied_coupons();

        $couponIds = get_posts([
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            'numberposts' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
            'suppress_filters' => false,
        ]);

        $items = [];
        $now = time();

        foreach ($couponIds as $couponId) {
            $coupon = new \WC_Coupon((int) $couponId);
            $code = wc_format_coupon_code((string) $coupon->get_code());
            if ('' === $code) {
                continue;
            }

            if (in_array($code, $appliedCoupons, true)) {
                continue;
            }

            $expiry = $coupon->get_date_expires();
            if ($expiry && $expiry->getTimestamp() < $now) {
                continue;
            }

            $usageLimit = (int) $coupon->get_usage_limit();
            $usageCount = (int) $coupon->get_usage_count();
            if ($usageLimit > 0 && $usageCount >= $usageLimit) {
                continue;
            }

            $emailRestrictions = $coupon->get_email_restrictions();
            if (!empty($emailRestrictions)) {
                continue;
            }

            $description = trim((string) $coupon->get_description());
            $amount = (float) $coupon->get_amount();
            $discountType = (string) $coupon->get_discount_type();

            if ('percent' === $discountType) {
                /* translators: %s: coupon percentage amount without symbol (e.g. 20). */
                $summary = sprintf(__('%s%% off', 'himuon-flex-cart'), wc_format_decimal($amount, 0));
            } elseif ('fixed_cart' === $discountType || 'fixed_product' === $discountType) {
                /* translators: %s: formatted currency amount (e.g. $10.00). */
                $summary = sprintf(__('%s off', 'himuon-flex-cart'), html_entity_decode(wp_strip_all_tags(wc_price($amount))));
            } elseif ($coupon->get_free_shipping()) {
                $summary = __('Free shipping', 'himuon-flex-cart');
            } else {
                $summary = '';
            }

            $items[] = [
                'code' => $code,
                'description' => $description,
                'summary' => $summary,
            ];
        }

        ob_start();
        Helper::template('coupon-list.php', [
            'items' => $items,
        ]);
        $html = (string) ob_get_clean();

        wp_send_json_success([
            'html' => $html,
        ]);
    }
}
