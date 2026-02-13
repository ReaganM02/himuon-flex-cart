<?php

use Himuon\Flex\Cart\Helper;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="himuon-flex-cart-plugin">
    <div class="himuon-cart--opacity"></div>
    <?php Helper::template('side-cart.php') ?>
    <div class="himuon-cart--min-cart-wrapper">
        <?php Helper::template('mini-cart.php') ?>
    </div>
</div>