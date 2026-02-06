<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="himuon-flex-cart-plugin">
    <div class="himuon-cart--opacity"></div>
    <?php require HIMUON_FLEX_CART_PATH . 'templates/side-cart.php'; ?>

    <div class="himuon-cart--min-cart-wrapper">
        <?php require HIMUON_FLEX_CART_PATH . 'templates/mini-cart.php'; ?>
    </div>
</div>