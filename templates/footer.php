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
$appliedCoupons = (function_exists('WC') && WC()->cart) ? WC()->cart->get_applied_coupons() : [];
?>
<footer class="himuon-cart--footer">
    <div class="himuon-cart-coupon-form-wrapper himuon-cart--total-breakdown-row">
        <div class="himuon-cart--breakdown-label">
            <?php echo esc_html(CouponView::couponHandlerLabelLeft()) ?>
        </div>
        <div>
            <?php echo esc_html(CouponView::couponHandlerLabelRight()) ?>
        </div>
    </div>
    <?php if (!empty($appliedCoupons)): ?>
        <div class="himuon-cart--total-breakdown-row himuon-cart--applied-coupons-row">
            <span class="himuon-cart--breakdown-label">
                <?php echo esc_html(CouponView::appliedCouponsLabel()); ?>
            </span>
            <div class="himuon-cart--applied-coupons">
                <?php foreach ($appliedCoupons as $couponCode): ?>
                    <?php $couponDisplay = wc_format_coupon_code($couponCode); ?>
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
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
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
    <a class="himuon-cart--checkout"
       href="<?php echo esc_url(wc_get_checkout_url()); ?>">
        <?php echo esc_html(SideCartView::checkoutLabel()) ?>
    </a>
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
