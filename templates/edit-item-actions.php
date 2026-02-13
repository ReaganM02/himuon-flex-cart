<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$buttonClass = isset($data['updateButtonClass']) && '' !== (string) $data['updateButtonClass']
    ? (string) $data['updateButtonClass']
    : 'himuon-cart--variation-update-cart-item';
?>
<div class="himuon-cart--variation-action">
    <?php if (!empty($data['permalink'])): ?>
        <a class="himuon-cart--variation-link"
           href="<?php echo esc_url($data['permalink']); ?>">
            <?php echo esc_html($data['viewLabel']); ?>
        </a>
    <?php endif; ?>
    <button type="button"
            class="<?php echo esc_attr($buttonClass); ?>"
            data-cart-item-key="<?php echo esc_attr($data['cartItemKey']); ?>">
        <?php echo esc_html($data['updateLabel']); ?>
    </button>
</div>
