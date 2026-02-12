<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (empty($data['show'])) {
    return;
}

$purchaseOptionsLabel = isset($data['purchaseOptionsLabel'])
    ? (string) $data['purchaseOptionsLabel']
    : esc_html__('Purchase options', 'himuon-flex-cart');
$singlePurchaseLabel = isset($data['singlePurchaseLabel'])
    ? (string) $data['singlePurchaseLabel']
    : esc_html__('One-time purchase', 'himuon-flex-cart');
$singlePurchasePrice = isset($data['singlePurchasePrice'])
    ? (string) $data['singlePurchasePrice']
    : '';
$selectedValue = isset($data['selectedValue'])
    ? (string) $data['selectedValue']
    : '0';
$options = isset($data['options']) && is_array($data['options'])
    ? $data['options']
    : [];
?>
<div class="himuon-cart--subscription-options" data-himuon-subscription-options>
    <div class="himuon-cart--subscription-label">
        <?php echo esc_html($purchaseOptionsLabel); ?>
    </div>

    <div class="himuon-cart--options-wrapper">
        <label class="himuon-cart--subscription-option">
            <input type="radio"
                   name="convert_to_sub"
                   value="0"
                   <?php checked('0', $selectedValue); ?>>
            <span class="himuon-cart--subscription-radio"></span>
            <span class="himuon-cart--subscription-option-text">
                <span class="himuon-cart--subscription-title">
                    <?php echo esc_html($singlePurchaseLabel); ?>
                </span>
                <?php if ($singlePurchasePrice !== ''): ?>
                    <span class="himuon-cart--subscription-detail">
                        <span class="himuon-cart--subscription-price">
                            <?php echo wp_kses_post($singlePurchasePrice); ?>
                        </span>
                    </span>
                <?php endif; ?>
            </span>
        </label>

        <?php foreach ($options as $option): ?>
            <?php
            $optionValue = isset($option['value']) ? (string) $option['value'] : '';
            $isChecked = isset($option['isChecked']) ? (bool) $option['isChecked'] : ($selectedValue === $optionValue);
            $title = isset($option['title']) ? (string) $option['title'] : '';
            $detail = isset($option['detail']) ? (string) $option['detail'] : '';
            $price = isset($option['price']) ? (string) $option['price'] : '';
            $discount = isset($option['discount']) ? (string) $option['discount'] : '';
            ?>
            <label class="himuon-cart--subscription-option"
                   data-scheme-key="<?php echo esc_attr($optionValue); ?>">
                <input type="radio"
                       name="convert_to_sub"
                       value="<?php echo esc_attr($optionValue); ?>"
                       <?php checked(true, $isChecked); ?>>
                <span class="himuon-cart--subscription-radio"></span>
                <span class="himuon-cart--subscription-option-text">
                    <?php if ($title !== ''): ?>
                        <span class="himuon-cart--subscription-title">
                            <?php echo esc_html($title); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($detail !== '' || $price !== '' || $discount !== ''): ?>
                        <span class="himuon-cart--subscription-detail">
                            <?php if ($detail !== ''): ?>
                                <?php echo esc_html($detail); ?>
                            <?php endif; ?>
                            <?php if ($price !== ''): ?>
                                <span class="himuon-cart--subscription-price">
                                    <?php echo wp_kses_post($price); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($discount !== ''): ?>
                                <span class="himuon-cart--subscription-discount">
                                    <?php echo esc_html($discount); ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <span class="himuon-cart--subscription-detail">
                            <?php echo esc_html__('Subscription option', 'himuon-flex-cart'); ?>
                        </span>
                    <?php endif; ?>
                </span>
            </label>
        <?php endforeach; ?>

        <?php if (empty($options)): ?>
            <label class="himuon-cart--subscription-option">
                <span class="himuon-cart--subscription-radio"></span>
                <span class="himuon-cart--subscription-option-text">
                    <span class="himuon-cart--subscription-title">
                        <?php echo esc_html__('Subscription plans', 'himuon-flex-cart'); ?>
                    </span>
                    <span class="himuon-cart--subscription-detail">
                        <?php echo esc_html__('Plans will appear here when loaded.', 'himuon-flex-cart'); ?>
                    </span>
                </span>
            </label>
        <?php endif; ?>
    </div>
</div>
