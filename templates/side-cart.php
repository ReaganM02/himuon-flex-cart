<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
$cart = WC()->cart;
$cartSubtotal = $cart ? $cart->get_cart_subtotal() : '';
$freeShippingThreshold = apply_filters('himuon_flex_cart_free_shipping_threshold', 120.00);

$freeShippingProgress = 0;

if ($freeShippingThreshold > 0 && $cart) {
    $currentTotal = (float) $cart->get_subtotal();
    $freeShippingProgress = min(100, ($currentTotal / $freeShippingThreshold) * 100);
}
?>
<aside id="himuon-side-cart"
       class="himuon-cart"
       aria-live="polite"
       aria-label="<?php echo esc_attr__('Shopping cart', 'himuon-flex-cart'); ?>">
    <div class="himuon-cart--loading"
         aria-hidden="true">
        <span class="himuon-cart--spinner"></span>
    </div>
    <header class="himuon-cart--header">
        <div>
            <h2 class="himuon-cart--title">
                <?php echo esc_html__('Your Cart', 'himuon-flex-cart'); ?>
            </h2>
        </div>
        <div class="himuon-cart--close">
            <svg xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="1">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
    </header>
    <?php if ($freeShippingThreshold > 0): ?>
        <section class="himuon-cart--progress">
            <p class="himuon-cart--progress-text">
                <?php
                if ($freeShippingProgress >= 100) {
                    echo esc_html__('You have free shipping!', 'himuon-flex-cart');
                } else {
                    $remaining = wc_price(max(0, $freeShippingThreshold - (float) $cart->get_subtotal()));
                    printf(
                        esc_html__('Add %s for free shipping', 'himuon-flex-cart'),
                        wp_kses_post($remaining)
                    );
                }
                ?>
            </p>
            <div class="himuon-cart--progress-bar"
                 role="progressbar"
                 aria-valuemin="0"
                 aria-valuemax="100"
                 aria-valuenow="<?php echo esc_attr((string) round($freeShippingProgress)); ?>">
                <span class="himuon-cart--progress-fill"
                      style="width: <?php echo esc_attr((string) round($freeShippingProgress)); ?>%;"></span>
            </div>
        </section>
    <?php endif; ?>

    <div class="himuon-cart--body">
        <?php if (empty($items)): ?>
            <p class="himuon-cart--empty"><?php echo esc_html__('Your cart is empty.', 'himuon-flex-cart'); ?></p>
        <?php else: ?>
            <ul class="himuon-cart--items">
                <?php
                foreach ($items as $cartItemKey => $cartItem) {
                    $product = $cartItem['data'];
                    if (!$product || !$product->exists() || $cartItem['quantity'] <= 0) {
                        continue;
                    }
                    require HIMUON_FLEX_CART_PATH . 'templates/cart-item.php';
                }
                ?>
            </ul>
        <?php endif; ?>
    </div>

    <?php if (!empty($items)): ?>
        <footer class="himuon-cart--footer">
            <div class="himuon-cart--totals">
                <span class="himuon-cart--totals-label"><?php echo esc_html__('Subtotal', 'himuon-flex-cart'); ?></span>
                <span class="himuon-cart--totals-value"><?php echo wp_kses_post($cartSubtotal); ?></span>
            </div>
            <a class="himuon-cart--checkout"
               href="<?php echo esc_url(wc_get_checkout_url()); ?>">
                <?php echo esc_html__('Checkout', 'himuon-flex-cart'); ?>
            </a>
        </footer>
    <?php endif; ?>
    <div class="himuon-cart--variation-selection">
        <div class="himuon-cart--spinner-wrapper">
            <div class="himuon-cart--variation-loading"
                 aria-hidden="true">
                <span class="himuon-cart--spinner"></span>
            </div>
        </div>
        <div class="himuon-cart--variation-content"></div>
    </div>
</aside>