<?php
use Himuon\Flex\Cart\Frontend\CartItemView;
use Himuon\Flex\Cart\Helper;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
$cart = WC()->cart;
$cartSubtotal = $cart ? $cart->get_cart_subtotal() : '';
?>
<aside id="himuon-side-cart"
       class="himuon-cart"
       aria-live="polite"
       aria-label="<?php echo esc_attr__('Shopping cart', 'himuon-flex-cart'); ?>">
    <div class="himuon-cart--loading"
         aria-hidden="true">
        <span class="himuon-cart--spinner"></span>
    </div>
    <?php
    // Cart Header
    $headerView = CartItemView::header();
    if ($headerView) {
        do_action('himuon_flex_cart_before_header');
        Helper::template('header.php', $headerView);
        do_action('himuon_flex_cart_after_header');
    }

    // Free Shipping
    $freeShippingView = CartItemView::freeShipping();
    if ($freeShippingView) {
        do_action('himuon_flex_cart_before_free_shipping');
        Helper::template('free-shipping.php', $freeShippingView);
        do_action('himuon_flex_cart_after_free_shipping');
    }
    ?>
    <div class="himuon-cart--body">
        <?php
        do_action('himuon_flex_cart_before_body');
        $itemsView = CartItemView::cartItems();
        ?>
        <?php if (empty($itemsView)): ?>
            <div class="himuon-cart--empty-message-wrapper">
                <div class="himuon-cart--empty">
                    <?php do_action('himuon_flex_cart_before_empty_message') ?>
                    <div class="himuon-cart--empty-message">
                        <?php
                        $emptyMessage = apply_filters(
                            'himuon_flex_cart_cart_empty',
                            __('Your cart is empty.', 'himuon-flex-cart')
                        );
                        echo esc_html($emptyMessage);
                        ?>
                    </div>
                    <?php do_action('himuon_flex_cart_after_empty_message') ?>
                </div>
            </div>
        <?php else: ?>
            <?php do_action('himuon_flex_cart_before_cart_items'); ?>
            <ul class="himuon-cart--items">
                <?php
                foreach ($itemsView as $cartItemView) {
                    Helper::template('cart-item.php', $cartItemView);
                }
                ?>
            </ul>
            <?php do_action('himuon_flex_cart_after_cart_items'); ?>
        <?php endif; ?>
        <?php do_action('himuon_flex_cart_after_body') ?>
    </div>
    <?php if (!empty($itemsView)): ?>
        <?php Helper::template('footer.php') ?>
    <?php endif; ?>
</aside>