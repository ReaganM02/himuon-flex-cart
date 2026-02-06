<?php
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
        <svg xmlns="http://www.w3.org/2000/svg"
             width="30"
             height="30"
             fill="currentColor"
             class="bi bi-basket2-fill"
             viewBox="0 0 16 16">
            <path
                  d="M5.929 1.757a.5.5 0 1 0-.858-.514L2.217 6H.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h.623l1.844 6.456A.75.75 0 0 0 3.69 15h8.622a.75.75 0 0 0 .722-.544L14.877 8h.623a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1.717L10.93 1.243a.5.5 0 1 0-.858.514L12.617 6H3.383zM4 10a1 1 0 0 1 2 0v2a1 1 0 1 1-2 0zm3 0a1 1 0 0 1 2 0v2a1 1 0 1 1-2 0zm4-1a1 1 0 0 1 1 1v2a1 1 0 1 1-2 0v-2a1 1 0 0 1 1-1" />
        </svg>
    </span>
    <span class="himuon-cart--mini-count"
          data-count="<?php echo esc_attr((string) absint($cartCount)); ?>">
        <?php echo esc_html((string) absint($cartCount)); ?>
    </span>
</div>