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
$variationAttributes = [];
$selectedVariations = [];
$parentProduct = null;

if ($product && $product->is_type('variation')) {
    $parentId = $product->get_parent_id();
    $parentProduct = $parentId ? wc_get_product($parentId) : null;
    if ($parentProduct && $parentProduct->is_type('variable')) {
        $variationAttributes = $parentProduct->get_variation_attributes();
        $selectedVariations = isset($cartItem['variation']) && is_array($cartItem['variation'])
            ? $cartItem['variation']
            : [];
    }
}
?>
<li class="himuon-cart--item">
    <div class="himuon-cart--details">
        <div class="himuon-cart--item-content">
            <a href="<?php echo esc_url(wc_get_cart_remove_url($cartItemKey)); ?>"
               class="himuon-cart--remove"
               aria-label="<?php echo esc_attr__('Remove item', 'himuon-flex-cart'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg"
                     width="16"
                     height="16"
                     fill="currentColor"
                     class="bi bi-trash"
                     viewBox="0 0 16 16">
                    <path
                          d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                    <path
                          d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                </svg>
            </a>
            <div class="himuon-cart--item-data">
                <?php if ($productPermalink): ?>
                    <a class="himuon-cart--name"
                       href="<?php echo esc_url($productPermalink); ?>">
                        <?php echo esc_html($product->get_name()); ?>
                    </a>
                <?php else: ?>
                    <span class="himuon-cart--name"><?php echo esc_html($product->get_name()); ?></span>
                <?php endif; ?>
                <div class="himuon-cart--price">
                    <?php echo wp_kses_post(WC()->cart->get_product_price($product)); ?>
                </div>
                <?php if (!empty($variationAttributes)): ?>
                    <div class="himuon-cart--variations">
                        <?php foreach ($variationAttributes as $attributeName => $options): ?>
                            <div class="himuon-cart--variation-select">
                                <?php
                                $selectedValue = isset($selectedVariations[$attributeName]) ? $selectedVariations[$attributeName] : '';
                                $attributeLabel = wc_attribute_label($attributeName, $parentProduct);
                                $fieldName = 'attribute_' . sanitize_title($attributeName);

                                if (taxonomy_exists($attributeName)) {
                                    $terms = wc_get_product_terms(
                                        $parentProduct->get_id(),
                                        $attributeName,
                                        ['fields' => 'all']
                                    );
                                    $options = array_map(function ($term) {
                                        return $term->slug;
                                    }, $terms);
                                } else {
                                    $options = is_array($options) ? $options : [];
                                }
                                ?>
                                <label class="himuon-cart--variation-label" for="<?php echo esc_attr($fieldName . '-' . $cartItemKey); ?>">
                                    <?php echo esc_html($attributeLabel); ?>:
                                </label>
                                <select class="himuon-cart--variation-dropdown"
                                        id="<?php echo esc_attr($fieldName . '-' . $cartItemKey); ?>"
                                        name="<?php echo esc_attr($fieldName); ?>"
                                        >
                                    <?php foreach ($options as $option): ?>
                                        <?php
                                        $optionValue = (string) $option;
                                        $optionLabel = $optionValue;
                                        if (taxonomy_exists($attributeName)) {
                                            $term = get_term_by('slug', $optionValue, $attributeName);
                                            if ($term && !is_wp_error($term)) {
                                                $optionLabel = $term->name;
                                            }
                                        }
                                        ?>
                                        <option value="<?php echo esc_attr($optionValue); ?>" <?php selected($selectedValue, $optionValue); ?>>
                                            <?php echo esc_html($optionLabel); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                           max="<?php echo absint($maxQuantity) ?>" />
                    <button type="button"
                            class="himuon-cart--plus"
                            aria-label="<?php echo esc_attr__('Increase quantity', 'himuon-flex-cart'); ?>">+</button>
                </div>
            </div>
        </div>
    </div>
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
</li>
