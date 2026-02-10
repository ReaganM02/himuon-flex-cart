<?php

use Himuon\Flex\Cart\Helper;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<header class="himuon-cart--header">
    <div class="himuon-cart--title-wrapper">
        <?php if (!empty($data['title'])): ?>
            <h2 class="himuon-cart--title">
                <?php echo esc_html($data['title']); ?>
            </h2>
        <?php endif; ?>

        <?php if ($data['showCounter']): ?>
            <span class="himuon-cart--item-counter">
                <?php echo absint($data['counter']) ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="himuon-cart--close">
        <?php echo wp_kses($data['closeIcon'], Helper::allowSVG()); ?>
    </div>
</header>