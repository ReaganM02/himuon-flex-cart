<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
$productPermalink = $product->is_visible() ? $product->get_permalink($cartItem) : '';
$thumbnail = $product->get_image('woocommerce_thumbnail', ['class' => 'himuon-cart--image']);
$lineSubtotal = WC()->cart->get_product_subtotal($product, $cartItem['quantity']);
$itemQuantity = (int) $cartItem['quantity'];

$maxQuantity = $product->get_max_purchase_quantity();
$minQuantity = $product->get_min_purchase_quantity();


$selectedVariations = [];
$displayVariations = [];
$dataAttributesMap = [];
$parentProduct = null;
$productTitle = $product ? $product->get_name() : '';



if ($product && $product->is_type('variation')) {
    $parentId = $product->get_parent_id();
    $parentProduct = $parentId ? wc_get_product($parentId) : null;
    if ($parentProduct) {
        $productTitle = $parentProduct->get_name();
    }
    if ($parentProduct && $parentProduct->is_type('variable')) {
        $selectedVariations = isset($cartItem['variation']) && is_array($cartItem['variation'])
            ? $cartItem['variation']
            : [];
    }
}
if (!empty($selectedVariations)) {
    foreach ($selectedVariations as $attributeKey => $selectedValue) {
        if (!$selectedValue) {
            continue;
        }
        $attributeName = $attributeKey;
        if (strpos($attributeKey, 'attribute_') === 0) {
            $attributeName = substr($attributeKey, strlen('attribute_'));
        }
        $attributeLabel = wc_attribute_label($attributeName, $parentProduct);
        $selectedLabel = $selectedValue;
        if (taxonomy_exists($attributeName)) {
            $term = get_term_by('slug', $selectedValue, $attributeName);
            if ($term && !is_wp_error($term)) {
                $selectedLabel = $term->name;
            }
        }
        $displayVariations[] = [
            'label' => $attributeLabel,
            'value' => $selectedLabel,
        ];
        $dataKey = sanitize_title($attributeName);
        if ($dataKey) {
            $dataAttributesMap[$dataKey] = $selectedValue;
        }
    }
    if (!empty($dataAttributesMap)) {
        $variationDataAttributes = ' data-attributes="' . esc_attr(wp_json_encode($dataAttributesMap)) . '"';
    }
}
?>
<li class="himuon-cart--cart-item">
    <div  class="himuon-cart--item">
        <div class="himuon-cart--media">
            <?php if ($productPermalink): ?>
                <a href="<?php echo esc_url($productPermalink); ?>"
                   class="himuon-cart--thumb">
                    <?php echo wp_kses_post($thumbnail); ?>
                </a>
            <?php else: ?>
                <span class="himuon-cart--thumb">
                    <?php echo wp_kses_post($thumbnail); ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="himuon-cart--item-content">
            <div class="himuon-cart--item-data">
                <?php if ($productPermalink): ?>
                    <a class="himuon-cart--name"
                       href="<?php echo esc_url($productPermalink); ?>">
                        <?php echo esc_html($productTitle); ?>
                    </a>
                <?php else: ?>
                    <span class="himuon-cart--name"><?php echo esc_html($productTitle); ?></span>
                <?php endif; ?>
                <?php if (!empty($displayVariations)): ?>
                    <div class="himuon-cart--variations"
                         data-product-id="<?php echo absint($product->get_parent_id()) ?>"
                         <?php echo $variationDataAttributes; ?>
                         data-cart-item-key="<?php echo esc_attr($cartItemKey) ?>">
                        <?php foreach ($displayVariations as $variation): ?>
                            <div class="himuon-cart--variation-data">
                                <span class="himuon-cart--variation-label">
                                    <?php echo esc_html($variation['label']); ?>:
                                </span>
                                <span class="himuon-cart--variation-value">
                                    <?php echo esc_html($variation['value']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi-chevron-right bi" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="himuon-cart--right-content">
                <div class="himuon-cart--quantity"
                     data-cart-item-key="<?php echo esc_attr($cartItemKey); ?>">
                    <button type="button"
                            aria-label="<?php echo esc_attr__('Decrease quantity', 'himuon-flex-cart'); ?>"
                            class="himuon-cart--minus">
                        -
                    </button>
                    <input type="number"
                           class="himuon-cart--qty"
                           value="<?php echo absint($itemQuantity) ?>"
                           min="<?php echo absint($minQuantity) ?>"
                           <?php echo ($maxQuantity > 0) ? "max=\"$maxQuantity\"" : '' ?> />
                    <button type="button"
                            class="himuon-cart--plus"
                            aria-label="<?php echo esc_attr__('Increase quantity', 'himuon-flex-cart'); ?>">+</button>
                </div>
                <div class="himuon-cart--price">
                    <?php echo wp_kses_post(WC()->cart->get_product_price($product)); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="himuon-cart--cart-item-action">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi-chevron-right bi" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
        </svg>
    </div>
    <div class="himuon-cart--actions">
       <div class="himuon-cart--actions-content">
         <div class="himuon-cart--action-delete" data-cart-item-key="<?php echo esc_attr($cartItemKey)?>">
            <?php echo esc_html__('Delete', 'himuon-flex-cart') ?>
        </div>
       </div>
    </div>
</li>