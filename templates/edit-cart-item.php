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

$context = [
    'cart_item_key' => $data['cartItemKey'] ?? '',
    'cart_item' => $data['cartItem'] ?? [],
    'product' => $data['product'] ?? null,
];

?>
<div class="himuon-cart--form-wrapper">
    <div class="himuon-cart--close-edit-panel">
       <?php echo wp_kses($data['closeEditPanel'], Helper::allowSVG()); ?>
    </div>
    <?php $initialImage = isset($data['initialImage']) && is_array($data['initialImage']) ? $data['initialImage'] : []; ?>

    <?php do_action('himuon_flex_cart_before_edit_panel_variation_image', $context, $data); ?>
    <div class="himuon-cart--variation-preview"
         data-initial-src="<?php echo esc_url($initialImage['src'] ?? ''); ?>"
         data-initial-alt="<?php echo esc_attr($initialImage['alt'] ?? ''); ?>">
        <?php if (!empty($initialImage['src'])): ?>
            <img class="himuon-cart--variation-preview-image"
                 src="<?php echo esc_url($initialImage['src']); ?>"
                 alt="<?php echo esc_attr($initialImage['alt'] ?? $data['title']); ?>">
        <?php endif; ?>
    </div>
    <?php do_action('himuon_flex_cart_after_edit_panel_variation_image', $data) ?>

    <?php do_action('himuon_flex_cart_before_edit_panel_title', $context, $data); ?>
    <div class="himuon-cart--variation-product-title">
        <?php echo esc_html($data['title']); ?>
    </div>
    <?php do_action('himuon_flex_cart_after_edit_panel_title', $context, $data); ?>
    <?php wc_get_template('single-product/add-to-cart/variable.php', $args); ?>
</div>
<?php
$actionData = $data;
$actionData['updateButtonClass'] = 'himuon-cart--variation-update-cart-item';
Helper::template('edit-item-actions.php', $actionData);
?>
