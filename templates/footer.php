<?php

use Himuon\Flex\Cart\Frontend\SideCartView;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$subtotal = SideCartView::getSubtotal();
$hasDiscount = SideCartView::hasDiscountTotal();
$discountTotal = $hasDiscount ? SideCartView::getDiscountTotal() : '';
?>
<footer class="himuon-cart--footer">
    <div class="himuon-cart-coupon-form-wrapper himuon-cart--total-breakdown-row">
        <div class="himuon-cart--breakdown-label">
            Have a voucher?
        </div>
        <div>
            Select
        </div>
    </div>
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
        <?php if (!empty($subtotal)): ?>
            <span class="himuon-cart--totals-label himuon-cart--breakdown-label">
                <?php echo esc_html(SideCartView::subtotalLabel()); ?>
            </span>
            <span class="himuon-cart--totals-value">
                <?php echo wp_kses_post($subtotal); ?>
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