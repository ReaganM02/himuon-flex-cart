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
    <div class="himuon-cart--variation-product-title">
        <?php echo esc_html($data['title']); ?>
    </div>
    <?php wc_get_template('single-product/add-to-cart/variable.php', $args); ?>
</div>
<div class="himuon-cart--variation-action">
    <?php if (!empty($data['permalink'])): ?>
        <a class="himuon-cart--variation-link"
           href="<?php echo esc_url($data['permalink']); ?>">
            <?php echo esc_html($data['viewLabel']) ?>
        </a>
    <?php endif; ?>
    <button type="button"
            class="himuon-cart--variation-update-cart-item"
            data-cart-item-key="<?php echo esc_attr($data['cartItemKey']) ?>">
        <?php echo esc_html($data['updateLabel']); ?>
    </button>
</div>