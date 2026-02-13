<?php

use Himuon\Flex\Cart\Helper;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="himuon-cart--form-wrapper">
    <div class="himuon-cart--close-edit-panel">
        <?php echo wp_kses($data['closeEditPanel'], Helper::allowSVG()); ?>
    </div>

    <div class="himuon-cart--variation-product-title">
        <?php echo esc_html($data['title']); ?>
    </div>

    <form class="himuon-cart--subscription-edit-form">
        <?php Helper::template('subscription-options.php', $data['subscriptionData']); ?>
    </form>
</div>
<?php
$actionData = $data;
$actionData['updateButtonClass'] = 'himuon-cart--subscription-update-cart-item';
Helper::template('edit-item-actions.php', $actionData);
?>