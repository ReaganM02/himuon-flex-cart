<?php

namespace Himuon\Flex\Cart\Frontend;

use Himuon\Flex\Cart\Frontend\CartItemView;
use WC_Product_Variable;
use WC_Product;

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
            'initialImage' => self::initialImageData($product, $parent),
            'viewLabel' => self::viewProductLabel($cartItem, $cartItemKey, $product),
            'updateLabel' => self::updateCartLabel($cartItem, $cartItemKey, $product),
            'closeEditPanel' => self::closeEditPanel($cartItem, $cartItemKey, $product)
        ];
    }

    private static function initialImageData(WC_Product $product, WC_Product_Variable $parent)
    {
        $imageProduct = $product->get_image_id() ? $product : $parent;
        $imageId = (int) $imageProduct->get_image_id();

        if (!$imageId) {
            return [
                'src' => wc_placeholder_img_src('woocommerce_thumbnail'),
                'srcset' => '',
                'sizes' => '',
                'alt' => $imageProduct->get_name(),
            ];
        }

        return [
            'src' => wp_get_attachment_image_url($imageId, 'woocommerce_thumbnail'),
            'srcset' => wp_get_attachment_image_srcset($imageId, 'woocommerce_thumbnail'),
            'sizes' => wp_get_attachment_image_sizes($imageId, 'woocommerce_thumbnail'),
            'alt' => get_post_meta($imageId, '_wp_attachment_image_alt', true) ?: $imageProduct->get_name(),
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

    public static function viewProductLabel(array $cartItem, string $cartItemKey, WC_Product $product)
    {
        return (string) apply_filters(
            'himuon_flex_cart_edit_cart_item_view_product_action',
            __('View Product', 'himuon-flex-cart'),
            $cartItem,
            $cartItemKey,
            $product
        );
    }

    public static function updateCartLabel(array $cartItem, string $cartItemKey, WC_Product $product)
    {
        return (string) apply_filters(
            'himuon_flex_cart_edit_cart_item_update_action',
            __('Update', 'himuon-flex-cart'),
            $cartItem,
            $cartItemKey,
            $product
        );
    }

    public static function closeEditPanel(array $cartItem, string $cartItemKey, WC_Product $product)
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
        </svg>';
        return (string) apply_filters(
            'himuon_flex_cart_close_edit_panel_icon',
            $svg,
            $cartItem,
            $cartItemKey,
            $product
        );
    }

}
