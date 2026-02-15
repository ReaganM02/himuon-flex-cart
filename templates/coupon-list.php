<?php

use Himuon\Flex\Cart\Frontend\CouponView;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
?>
<?php if (empty($items)): ?>
    <div class="himuon-cart--coupon-list-empty">
        <?php echo esc_html__('No coupons available right now.', 'himuon-flex-cart'); ?>
    </div>
<?php else: ?>
    <?php foreach ($items as $item): ?>
        <?php
        $code = isset($item['code']) ? (string) $item['code'] : '';
        $description = isset($item['description']) ? (string) $item['description'] : '';
        $summary = isset($item['summary']) ? (string) $item['summary'] : '';
        $meta = '' !== $description ? $description : $summary;
        ?>
        <div class="himuon-cart--coupon-list-item">
            <div class="himuon-cart--coupon-circle-right"></div>
            <div class="himuon-cart--coupon-list-main">
                <div>
                    <?php if ('' !== $meta): ?>
                        <div class="himuon-cart--coupon-list-description">
                            <?php echo esc_html($meta); ?>
                        </div>
                    <?php endif; ?>
                    <div class="himuon-cart--coupon-list-code">
                        <?php echo esc_html($code); ?>
                    </div>
                </div>
                <button type="button"
                        class="himuon-cart--coupon-list-apply"
                        data-coupon-code="<?php echo esc_attr($code); ?>">
                    <?php echo esc_html(CouponView::applyCouponLabel()); ?>
                </button>
            </div>

        </div>
    <?php endforeach; ?>
<?php endif; ?>