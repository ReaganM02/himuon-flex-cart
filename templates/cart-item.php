<?php

use Himuon\Flex\Cart\Helper;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<li class="himuon-cart--cart-item">
    <div class="himuon-cart--item">
        <?php if (!empty($data['thumbnail'])): ?>
            <div class="himuon-cart--media">
                <?php if (!empty($data['permalink'])): ?>
                    <a href="<?php echo esc_url($data['permalink']); ?>"
                       class="himuon-cart--thumb">
                        <?php echo wp_kses_post($data['thumbnail']); ?>
                    </a>
                <?php else: ?>
                    <span class="himuon-cart--thumb">
                        <?php echo wp_kses_post($data['thumbnail']); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="himuon-cart--item-content">
            <div class="himuon-cart--item-data">
                <?php do_action('himuon_flex_cart_before_cart_item_title', $data); ?>
                <?php if (!empty($data['permalink'])): ?>
                    <a class="himuon-cart--name"
                       href="<?php echo esc_url($data['permalink']); ?>">
                        <?php echo esc_html($data['title']); ?>
                    </a>
                <?php else: ?>
                    <span class="himuon-cart--name">
                        <?php echo esc_html($data['title']); ?>
                    </span>
                <?php endif; ?>
                <?php do_action('himuon_flex_cart_after_cart_item_title', $data); ?>
                <!-- Start Of Variation -->
                <?php if (!empty($data['variation'])): ?>
                    <?php do_action('himuon_flex_cart_before_cart_item_variation', $data); ?>
                    <div class="himuon-cart--variations">
                        <?php echo esc_html($data['variation']) ?>
                    </div>
                    <?php do_action('himuon_flex_cart_after_cart_item_variation', $data); ?>
                <?php endif; ?>
                <!-- End of Variation -->

                <!-- Start of Subscription -->
                <?php if (!empty($data['subscription'])): ?>
                    <?php do_action('himuon_flex_cart_before_cart_item_subscription', $data); ?>
                    <div class="himuon-cart--purchase-type">
                        <?php echo esc_html($data['subscription']) ?>
                    </div>
                    <?php do_action('himuon_flex_cart_after_cart_item_subscription', $data); ?>
                <?php endif; ?>
                <!-- End of Subscription -->
            </div>
            <div class="himuon-cart--right-content">
                <?php do_action('himuon_flex_cart_before_cart_item_quantity', $data); ?>
                <div class="himuon-cart--quantity"
                     data-cart-item-key="<?php echo esc_attr($data['cartItemKey']); ?>">
                    <button type="button"
                            aria-label="<?php echo esc_attr__('Decrease quantity', 'himuon-flex-cart'); ?>"
                            class="himuon-cart--minus">
                        <?php echo wp_kses($data['quantity']['minus'], Helper::allowSVG()); ?>
                    </button>
                    <input type="number"
                           class="himuon-cart--qty"
                           value="<?php echo absint($data['quantity']['qty']) ?>"
                           min="<?php echo absint($data['quantity']['min']) ?>"
                           <?php echo ($data['quantity']['max'] > 0) ? 'max="' . $data['quantity']['max'] . '"' : '' ?> />
                    <button type="button"
                            class="himuon-cart--plus"
                            aria-label="<?php echo esc_attr__('Increase quantity', 'himuon-flex-cart'); ?>">
                        <?php echo wp_kses($data['quantity']['plus'], Helper::allowSVG()); ?>
                    </button>
                </div>
                <?php do_action('himuon_flex_cart_before_after_item_quantity', $data); ?>
                <div class="himuon-cart--price">
                    <?php echo wc_price($data['price']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="himuon-cart--cart-item-action">
        <?php echo wp_kses($data['actionHandler'], Helper::allowSVG()); ?>
    </div>
    <div class="himuon-cart--actions">
        <div class="himuon-cart--actions-content">
            <?php
            foreach ($data['actions'] as $action) {
                if ($action['id'] === 'edit') {
                    if (!empty($data['subscription']) || !empty($data['variation'])) {
                        ?>
                        <div class="<?php echo esc_attr($action['class']) ?>"
                             data-cart-item-key="<?php echo esc_attr($data['cartItemKey']) ?>">
                            <?php echo esc_html($action['label']) ?>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="<?php echo esc_attr($action['class']) ?>"
                         data-cart-item-key="<?php echo esc_attr($data['cartItemKey']) ?>">
                        <?php echo esc_html($action['label']) ?>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</li>