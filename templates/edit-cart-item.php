<?php

use Himuon\Flex\Cart\Helper;

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
    <div class="himuon-cart--close-edit-panel">
       <?php echo wp_kses($data['closeEditPanel'], Helper::allowSVG()); ?>
    </div>
    <?php $initialImage = isset($data['initialImage']) && is_array($data['initialImage']) ? $data['initialImage'] : []; ?>
    <div class="himuon-cart--variation-preview"
         data-initial-src="<?php echo esc_url($initialImage['src'] ?? ''); ?>"
         data-initial-srcset="<?php echo esc_attr($initialImage['srcset'] ?? ''); ?>"
         data-initial-sizes="<?php echo esc_attr($initialImage['sizes'] ?? ''); ?>"
         data-initial-alt="<?php echo esc_attr($initialImage['alt'] ?? ''); ?>">
        <?php if (!empty($initialImage['src'])): ?>
            <img class="himuon-cart--variation-preview-image"
                 src="<?php echo esc_url($initialImage['src']); ?>"
                 <?php if (!empty($initialImage['srcset'])): ?>srcset="<?php echo esc_attr($initialImage['srcset']); ?>"<?php endif; ?>
                 <?php if (!empty($initialImage['sizes'])): ?>sizes="<?php echo esc_attr($initialImage['sizes']); ?>"<?php endif; ?>
                 alt="<?php echo esc_attr($initialImage['alt'] ?? $data['title']); ?>">
        <?php endif; ?>
    </div>
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
