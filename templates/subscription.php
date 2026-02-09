<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
$displayProduct = isset($variation) && $variation ? $variation : $product;
?>
<?php if (class_exists('WCS_ATT_Product_Schemes') && WCS_ATT_Product_Schemes::has_subscription_schemes($displayProduct)): ?>
    <?php
    $schemes = WCS_ATT_Product_Schemes::get_subscription_schemes($displayProduct);
    $defaultSchemeKey = WCS_ATT_Product_Schemes::get_default_subscription_scheme($displayProduct, 'key');
    $selectedValue = $defaultSchemeKey ? WCS_ATT_Product_Schemes::stringify_subscription_scheme_key($defaultSchemeKey) : '0';
    ?>
    <div class="himuon-cart--subscription-options"
         data-himuon-subscription-options>
        <div class="himuon-cart--subscription-label">
            <?php echo esc_html__('Purchase options', 'himuon-flex-cart'); ?>
        </div>
        <div class="himuon-cart--options-wrapper">
            <label class="himuon-cart--subscription-option">
                <input type="radio"
                       name="convert_to_sub"
                       value="0"
                       <?php checked('0', $selectedValue); ?>>
                <span class="himuon-cart--subscription-radio"></span>       
                <span
                      class="himuon-cart--subscription-title"><?php echo esc_html__('Single purchase', 'himuon-flex-cart'); ?></span>
            </label>
                <?php foreach ($schemes as $schemeKey => $scheme): ?>
                    <?php
                    $interval = absint($scheme->get_interval());
                    $period = $scheme->get_period();
                    $periodLabel = $period ? $period : '';
                    if ($interval > 1 && $periodLabel && !str_ends_with($periodLabel, 's')) {
                        $periodLabel .= 's';
                    }
                    $groupLabel = $period ? sprintf(esc_html__('%s delivery', 'himuon-flex-cart'), ucfirst($period)) : esc_html__('Subscription', 'himuon-flex-cart');
                    $optionValue = WCS_ATT_Product_Schemes::stringify_subscription_scheme_key($schemeKey);
                    $basePrice = (float) $displayProduct->get_price();
                    $schemePrice = '';
                    if (class_exists('WCS_ATT_Product_Prices')) {
                        $schemePrice = WCS_ATT_Product_Prices::get_price($displayProduct, $schemeKey);
                    }
                    if ('' === $schemePrice || null === $schemePrice) {
                        $schemePrice = $basePrice;
                    }
                    if (method_exists($scheme, 'get_pricing_mode') && method_exists($scheme, 'get_discount')) {
                        $pricingMode = $scheme->get_pricing_mode();
                        $discount = $scheme->get_discount();
                        if ('inherit' === $pricingMode && $discount) {
                            $schemePrice = $basePrice * (100 - (float) $discount) / 100;
                        }
                    }
                    $displayPrice = wc_price(wc_get_price_to_display($displayProduct, ['price' => $schemePrice]));
                    $discount = method_exists($scheme, 'get_discount') ? $scheme->get_discount() : false;
                    $discountText = $discount ? sprintf(esc_html__(' (%s%% off)', 'himuon-flex-cart'), round($discount, 1)) : '';
                    $detailLabel = $interval > 1 && $periodLabel
                        ? sprintf(esc_html__('Every %1$d %2$s', 'himuon-flex-cart'), $interval, $periodLabel)
                        : ($periodLabel ? sprintf(esc_html__('Every %s', 'himuon-flex-cart'), $periodLabel) : esc_html__('Every period', 'himuon-flex-cart'));
                    ?>
                        <label class="himuon-cart--subscription-option"
                               data-scheme-key="<?php echo esc_attr($optionValue); ?>">
                            <input type="radio"
                                   name="convert_to_sub"
                                   value="<?php echo esc_attr($optionValue); ?>"
                                   <?php checked($selectedValue, $optionValue); ?>>
                            <span class="himuon-cart--subscription-radio"></span>       
                            <span class="himuon-cart--subscription-option-text">
                                <span class="himuon-cart--subscription-title"><?php echo esc_html($groupLabel); ?></span>
                                    <span class="himuon-cart--subscription-detail">
                                    <?php echo esc_html($detailLabel); ?>
                                        <span class="himuon-cart--subscription-price-prefix">
                                            <?php echo esc_html__(' for ', 'himuon-flex-cart'); ?>
                                        </span>
                                        <span class="himuon-cart--subscription-price">
                                            <?php echo wp_kses_post($displayPrice); ?>
                                        </span>
                                        <?php if ($discountText): ?>
                                            <span class="himuon-cart--subscription-discount">
                                                <?php echo esc_html($discountText); ?>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                            </span>       
                        </label>
                <?php endforeach; ?>
            </div>
        </div>
<?php endif; ?>