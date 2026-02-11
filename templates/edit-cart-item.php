<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
$args = [
    'product' => $data['product'],
    'attributes' => $data['attributes'],
    'available_variations' => $data['available_variations'],
];
?>
<div class="himuon-cart--form-wrapper">
    <div class="himuon-cart--variation-product-title"><?php echo esc_attr($data['product']->get_name()); ?></div>
    <?php wc_get_template('single-product/add-to-cart/variable.php', $args); ?>
</div>
<div class="himuon-cart--variation-action">
    <a class="himuon-cart--variation-link"
       href="<?php echo get_permalink($productID) ?>">
        <?php echo esc_html__('View Product', 'himuon-flex-cart'); ?>
    </a>
    <button type="button"
            class="himuon-cart--variation-update-cart-item"
            data-cart-item-key="<?php echo esc_attr($data['cartItemKey']) ?>">
        <?php echo esc_html__('Update Item', 'himuon-flex-cart'); ?>
    </button>
</div>