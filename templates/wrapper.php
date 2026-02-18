<?php

use Himuon\Flex\Cart\Helper;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$himuonFlexCartCart = (function_exists('WC') && WC()->cart) ? WC()->cart : null;
$himuonFlexCartShowMiniCart = (bool) apply_filters(
    'himuon_flex_cart_show_mini_cart',
    true,
    $himuonFlexCartCart
);
?>
<div class="himuon-flex-cart-plugin">
    <div class="himuon-cart--opacity"></div>
    <?php Helper::template('side-cart.php') ?>
    <?php if ($himuonFlexCartShowMiniCart): ?>
        <div class="himuon-cart--min-cart-wrapper">
            <?php Helper::template('mini-cart.php') ?>
        </div>
    <?php endif; ?>
</div>
