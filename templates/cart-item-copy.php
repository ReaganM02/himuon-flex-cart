<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
$productPermalink = $product->is_visible() ? $product->get_permalink($cartItem) : '';
$thumbnail = $product->get_image('woocommerce_thumbnail', ['class' => 'himuon-cart--image']);
$lineSubtotal = WC()->cart->get_product_subtotal($product, $cartItem['quantity']);
?>
<li class="himuon-cart--item">
    <a class="himuon-cart--remove"
       href="<?php echo esc_url(wc_get_cart_remove_url($cartItemKey)); ?>"
       aria-label="<?php echo esc_attr__('Remove item', 'himuon-flex-cart'); ?>">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
            <path d="M3 6h18M9 6V4h6v2M8 6l1 14h6l1-14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </a>
    <div class="himuon-cart--details">
        <?php if ($productPermalink): ?>
            <a class="himuon-cart--name"
               href="<?php echo esc_url($productPermalink); ?>">
                <?php echo esc_html($product->get_name()); ?>
            </a>
        <?php else: ?>
            <span class="himuon-cart--name"><?php echo esc_html($product->get_name()); ?></span>
        <?php endif; ?>
        <?php
        $itemData = wc_get_formatted_cart_item_data($cartItem, true);
        if ($itemData):
            ?>
            <div class="himuon-cart--variation">
                <?php echo wp_kses_post($itemData); ?>
            </div>
        <?php endif; ?>
        <div class="himuon-cart--price"><?php echo wp_kses_post(WC()->cart->get_product_price($product)); ?></div>
        <div class="himuon-cart--qty">
            <button class="himuon-cart--qty-btn" type="button" aria-label="<?php echo esc_attr__('Decrease quantity', 'himuon-flex-cart'); ?>">−</button>
            <span class="himuon-cart--qty-value"><?php echo esc_html((string) $cartItem['quantity']); ?></span>
            <button class="himuon-cart--qty-btn" type="button" aria-label="<?php echo esc_attr__('Increase quantity', 'himuon-flex-cart'); ?>">+</button>
        </div>
    </div>
    <div class="himuon-cart--media">
        <?php if ($productPermalink): ?>
            <a href="<?php echo esc_url($productPermalink); ?>"
               class="himuon-cart--thumb">
                <?php echo wp_kses_post($thumbnail); ?>
            </a>
        <?php else: ?>
            <span class="himuon-cart--thumb">
                <?php echo wp_kses_post($thumbnail); ?>
            </span>
        <?php endif; ?>
        <div class="himuon-cart--line-subtotal"><?php echo wp_kses_post($lineSubtotal); ?></div>
    </div>
</li>
