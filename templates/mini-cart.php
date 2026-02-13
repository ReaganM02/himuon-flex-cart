<?php

use Himuon\Flex\Cart\Frontend\CartItemView;
use Himuon\Flex\Cart\Helper;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$cartCount = 0;
if (function_exists('WC') && WC()->cart) {
    $cartCount = WC()->cart->get_cart_contents_count();
}
?>
<div id="himuon-mini-cart"
     class="himuon-cart--mini himuon-side-cart-handler"
     type="button"
     aria-label="<?php echo esc_attr__('Open cart', 'himuon-flex-cart'); ?>">
    <span class="himuon-cart--mini-icon"
          aria-hidden="true">
        <?php echo wp_kses(CartItemView::miniCartIcon(), Helper::allowSVG()) ?>
    </span>
    <span class="himuon-cart--mini-count"
          data-count="<?php echo esc_attr((string) absint($cartCount)); ?>">
        <?php echo esc_html((string) absint($cartCount)); ?>
    </span>
</div>