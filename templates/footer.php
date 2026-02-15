<?php

use Himuon\Flex\Cart\Frontend\CouponView;
use Himuon\Flex\Cart\Frontend\SideCartView;
use Himuon\Flex\Cart\Helper;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$payableTotal = SideCartView::getPayableTotal();
$hasDiscount = SideCartView::hasDiscountTotal();
$discountTotal = $hasDiscount ? SideCartView::getDiscountTotal() : '';
$cart = (function_exists('WC') && WC()->cart) ? WC()->cart : null;
$appliedCoupons = $cart ? $cart->get_applied_coupons() : [];
?>
<footer class="himuon-cart--footer">
    <?php do_action('himuon_flex_cart_footer_start', $cart); ?>

    <?php do_action('himuon_flex_cart_footer_before_coupon_trigger', $cart); ?>
    <div class="himuon-cart-coupon-form-wrapper himuon-cart--total-breakdown-row">
        <div class="himuon-cart--breakdown-label">
            <?php echo esc_html(CouponView::couponHandlerLabelLeft()) ?>
        </div>
        <div>
            <?php echo esc_html(CouponView::couponHandlerLabelRight()) ?>
        </div>
    </div>
    <?php do_action('himuon_flex_cart_footer_after_coupon_trigger', $cart); ?>

    <?php do_action('himuon_flex_cart_footer_before_applied_coupons', $appliedCoupons, $cart); ?>
    <?php if (!empty($appliedCoupons)): ?>
        <?php foreach ($appliedCoupons as $couponCode): ?>
            <?php
            $couponDisplay = wc_format_coupon_code($couponCode);
            $excludeTax = true;
            if ($cart && method_exists($cart, 'display_prices_including_tax')) {
                $excludeTax = !$cart->display_prices_including_tax();
            }
            $couponAmount = $cart ? (float) $cart->get_coupon_discount_amount($couponCode, $excludeTax) : 0.0;
            do_action('himuon_flex_cart_footer_coupon_row', $couponCode, $couponAmount, $cart);
            ?>
            <div class="himuon-cart--total-breakdown-row himuon-cart--applied-coupon-line">
                <div class="himuon-cart--applied-coupon-left">
                    <span class="himuon-cart--breakdown-label">
                        <?php echo esc_html(CouponView::appliedCouponsLabel()); ?>
                    </span>
                    <button type="button"
                            class="himuon-cart--applied-coupon-remove"
                            data-coupon-code="<?php echo esc_attr($couponCode); ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('Remove coupon %s', 'himuon-flex-cart'), $couponDisplay)); ?>">
                        <span class="himuon-cart--applied-coupon-code">
                            <?php echo esc_html($couponDisplay); ?>
                        </span>
                        <span class="himuon-cart--applied-coupon-remove-icon">
                            <?php echo wp_kses(CouponView::removeCouponIcon(), Helper::allowSVG()); ?>
                        </span>
                    </button>
                </div>
                <span class="himuon-cart--applied-coupon-value">
                    - <?php echo wp_kses_post(wc_price($couponAmount)); ?>
                </span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php do_action('himuon_flex_cart_footer_after_applied_coupons', $appliedCoupons, $cart); ?>

    <?php do_action('himuon_flex_cart_footer_before_discount', $discountTotal, $cart); ?>
    <?php if ($hasDiscount): ?>
        <div class="himuon-cart--total-breakdown-row">
            <span class="himuon-cart--discount-total-label himuon-cart--breakdown-label">
                <?php echo esc_html(SideCartView::discountLabel()); ?>
            </span>
            <span class="himuon-cart--discount-total-value">
                <?php echo wp_kses_post($discountTotal); ?>
            </span>
        </div>
    <?php endif; ?>
    <?php do_action('himuon_flex_cart_footer_after_discount', $discountTotal, $cart); ?>

    <?php do_action('himuon_flex_cart_footer_before_total', $payableTotal, $cart); ?>
    <div class="himuon-cart--total-breakdown-row">
        <?php if (!empty($payableTotal)): ?>
            <span class="himuon-cart--totals-label himuon-cart--breakdown-label">
                <?php echo esc_html(SideCartView::subtotalLabel()); ?>
            </span>
            <span class="himuon-cart--totals-value">
                <?php echo wp_kses_post($payableTotal); ?>
            </span>
        <?php endif; ?>
    </div>
    <?php do_action('himuon_flex_cart_footer_after_total', $payableTotal, $cart); ?>

    <?php do_action('himuon_flex_cart_footer_before_checkout', $cart); ?>
    <a class="himuon-cart--checkout"
       href="<?php echo esc_url(wc_get_checkout_url()); ?>">
        <?php echo esc_html(SideCartView::checkoutLabel()) ?>
    </a>
    <?php do_action('himuon_flex_cart_footer_after_checkout', $cart); ?>
    <?php do_action('himuon_flex_cart_footer_end', $cart); ?>
</footer>
<div class="himuon-cart--edit-item-wrapper">
    <div class="himuon-cart--edit-item-overlay"></div>
    <div class="himuon-cart--edit-content">
    </div>
</div>
<div class="himuon-cart--coupon-item-wrapper">
    <div class="himuon-cart--coupon-item-overlay"></div>
    <div class="himuon-cart--coupon-content">
        <?php Helper::template('coupon.php'); ?>
    </div>
</div>