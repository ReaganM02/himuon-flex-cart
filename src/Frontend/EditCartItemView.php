<?php

namespace Himuon\Flex\Cart\Frontend;

use WC_Product;
use WC_Product_Variable;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class EditCartItemView
{
    public static function build(array $cartItem, string $cartItemKey)
    {
        if (empty($cartItem['data']) || !($cartItem['data'] instanceof WC_Product)) {
            return false;
        }

        $product = $cartItem['data'];
        $parent = self::resolveParentVariableProduct($product);

        if (!$parent) {
            return false;
        }

        return [
            'product' => $parent,
            'cartItem' => $cartItem,
            'cartItemKey' => $cartItemKey,
            'title' => CartItemView::title($cartItemKey, $cartItem, $parent),
            'permalink' => CartItemView::permalink($cartItemKey, $cartItem, $parent),
            'attributes' => $parent->get_variation_attributes(),
            'available_variations' => $parent->get_available_variations(),
            'viewLabel' => self::viewProductLabel($cartItem, $cartItemKey, $product),
            'updateLabel' => self::updateCartLabel($cartItem, $cartItemKey, $product)
        ];
    }

    private static function resolveParentVariableProduct(WC_Product $product)
    {
        $parentId = $product->get_parent_id();
        $parent = $parentId ? wc_get_product($parentId) : null;

        if (!$parent || !$parent instanceof WC_Product_Variable || !$parent->is_type('variable')) {
            return null;
        }

        return $parent;
    }

    private static function viewProductLabel(array $cartItem, string $cartItemKey, WC_Product $product)
    {
        return (string) apply_filters(
            'himuon_flex_cart_edit_cart_item_view_product_action',
            __('View Product', 'himuon-flex-cart'),
            $cartItem,
            $cartItemKey,
            $product
        );
    }

    private static function updateCartLabel(array $cartItem, string $cartItemKey, WC_Product $product)
    {
        return (string) apply_filters(
            'himuon_flex_cart_edit_cart_item_update_action',
            __('Update', 'himuon-flex-cart'),
            $cartItem,
            $cartItemKey,
            $product
        );
    }

}
