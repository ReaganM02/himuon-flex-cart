<?php

use Himuon\Flex\Cart\Frontend\CouponView;
use Himuon\Flex\Cart\Helper;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$isCouponListEnabled = (bool) apply_filters('himuon_flex_cart_enable_coupon_list', true);
?>
<div class="himuon-cart--form-wrapper himuon-cart--coupon-form-wrapper">
    <?php do_action('himuon_flex_cart_coupon_panel_start'); ?>
    <div class="himuon-cart--close-coupon-panel"
         aria-label="Close coupon panel">
        <?php echo wp_kses(CouponView::closeCouponPanelIcon(), Helper::allowSVG()) ?>
    </div>

    <?php do_action('himuon_flex_cart_coupon_before_title'); ?>
    <div class="himuon-cart--coupon-title">
        <?php echo esc_html(CouponView::couponTitle()) ?>
    </div>
    <?php do_action('himuon_flex_cart_coupon_after_title'); ?>

    <?php do_action('himuon_flex_cart_coupon_before_notices'); ?>
    <div class="himuon-cart--coupon-notices"
         aria-live="polite"></div>
    <?php do_action('himuon_flex_cart_coupon_after_notices'); ?>

    <?php do_action('himuon_flex_cart_coupon_before_form'); ?>
    <form class="himuon-cart--coupon-panel-form"
          method="post">
        <?php do_action('himuon_flex_cart_coupon_form_start'); ?>
        <input id="himuon-side-cart-coupon"
               class="himuon-cart--coupon-input"
               type="text"
               name="coupon_code"
               placeholder="Enter coupon code"
               autocomplete="off" />
        <button type="submit"
                class="himuon-cart--coupon-submit"
                disabled>
            <?php echo wp_kses(CouponView::applyCouponLabel(), Helper::allowSVG()) ?>
        </button>
        <?php do_action('himuon_flex_cart_coupon_form_end'); ?>
    </form>
    <?php do_action('himuon_flex_cart_coupon_after_form'); ?>

    <?php if ($isCouponListEnabled): ?>
        <?php do_action('himuon_flex_cart_coupon_before_list'); ?>
        <div class="himuon-cart--coupon-list"
             aria-live="polite"></div>
        <?php do_action('himuon_flex_cart_coupon_after_list'); ?>

        <?php do_action('himuon_flex_cart_coupon_before_list_loading'); ?>
        <div class="himuon-cart--coupon-list-loading"
             hidden
             aria-hidden="true">
            <div class="himuon-cart--coupon-list-loading-item">
                <div class="himuon-cart--coupon-list-loading-main">
                    <div class="himuon-cart--coupon-list-loading-code"></div>
                    <div class="himuon-cart--coupon-list-loading-description"></div>
                </div>
                <div class="himuon-cart--coupon-list-loading-apply"></div>
            </div>
            <div class="himuon-cart--coupon-list-loading-item">
                <div class="himuon-cart--coupon-list-loading-main">
                    <div class="himuon-cart--coupon-list-loading-code"></div>
                    <div class="himuon-cart--coupon-list-loading-description"></div>
                </div>
                <div class="himuon-cart--coupon-list-loading-apply"></div>
            </div>
            <div class="himuon-cart--coupon-list-loading-item">
                <div class="himuon-cart--coupon-list-loading-main">
                    <div class="himuon-cart--coupon-list-loading-code"></div>
                    <div class="himuon-cart--coupon-list-loading-description"></div>
                </div>
                <div class="himuon-cart--coupon-list-loading-apply"></div>
            </div>
            <div class="himuon-cart--coupon-list-loading-item">
                <div class="himuon-cart--coupon-list-loading-main">
                    <div class="himuon-cart--coupon-list-loading-code"></div>
                    <div class="himuon-cart--coupon-list-loading-description"></div>
                </div>
                <div class="himuon-cart--coupon-list-loading-apply"></div>
            </div>
        </div>
        <?php do_action('himuon_flex_cart_coupon_after_list_loading'); ?>
    <?php endif; ?>
    <?php do_action('himuon_flex_cart_coupon_panel_end'); ?>
</div>