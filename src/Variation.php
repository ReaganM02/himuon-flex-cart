<?php

namespace Himuon\Flex\Cart;

use WC_Product_Variable;
use WCS_ATT_Product_Price_Filters;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Variation
{
    public static function getForm($cartItem, $cartItemKey)
    {
        if (!$cartItem || empty($cartItem['data']) || !($cartItem['data'] instanceof \WC_Product)) {
            return false;
        }

        $product = $cartItem['data'];

        $parentId = $product->get_parent_id();
        $parent = $parentId ? wc_get_product($parentId) : null;

        if (!$parent || !$parent->is_type('variable') || !$parent instanceof WC_Product_Variable) {
            return false;
        }

        $previousProduct = isset($GLOBALS['product']) ? $GLOBALS['product'] : null;
        $GLOBALS['product'] = $parent;

        $removedSubscriptionOptionsLayout = Subscription::removeSubscriptionOptionsLayout();
        $removedPriceFilters = Subscription::removeSubscriptionPriceFilters();

        self::addVariationDescriptionFilter();

        $args = [
            'product' => $parent,
            'attributes' => $parent->get_variation_attributes(),
            'available_variations' => $parent->get_available_variations(),
            'cartItemKey' => $cartItemKey
        ];

        remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);
        try {
            ob_start();
            Helper::template('edit-cart-item.php', $args);
            $form = ob_get_clean();
        } finally {

            add_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);

            if ($removedPriceFilters) {
                Subscription::addSubscriptionPriceFilters();
            }

            if ($removedSubscriptionOptionsLayout) {
                Subscription::addSubscriptionOptionsLayout();
            }

            if (null !== $previousProduct) {
                $GLOBALS['product'] = $previousProduct;
            } else {
                unset($GLOBALS['product']);
            }

            self::removeVariationDescriptionFilter();
        }

        return $form;
    }

    public static function addVariationDescriptionFilter()
    {
        add_filter('woocommerce_available_variation', [self::class, 'variationData'], 10, 3);
    }

    public static function removeVariationDescriptionFilter()
    {
        remove_filter('woocommerce_available_variation', [self::class, 'variationData'], 10);
    }

    public static function variationData($variationData, $product, $variation)
    {
        // Remove description
        $variationData['variation_description'] = '';
        return $variationData;
    }


}
