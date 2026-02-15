<?php

use Himuon\Flex\Cart\Frontend\CouponView;
use Himuon\Flex\Cart\Helper;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="himuon-cart--form-wrapper himuon-cart--coupon-form-wrapper">
    <div class="himuon-cart--close-coupon-panel"
         aria-label="Close coupon panel">
        <?php echo wp_kses(CouponView::closeCouponPanelIcon(), Helper::allowSVG()) ?>
    </div>
    <div class="himuon-cart--coupon-title">
        <?php echo CouponView::couponTitle() ?>
    </div>
    <div class="himuon-cart--coupon-notices"
         aria-live="polite"></div>
    <form class="himuon-cart--coupon-panel-form"
          method="post">
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
    </form>
</div>