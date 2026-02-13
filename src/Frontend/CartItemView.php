<?php

namespace Himuon\Flex\Cart\Frontend;

use WC_Product;
use WC_Product_Variation;
use WCS_ATT_Product_Schemes;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}


final class CartItemView
{
    private static function getCart()
    {
        if (!function_exists('WC') || !WC()->cart) {
            return null;
        }
        return WC()->cart;
    }

    public static function freeShipping()
    {
        $threshold = apply_filters('himuon_flex_cart_free_shipping_threshold', 100.00);
        $progress = 0;

        $cart = self::getCart();

        if ($threshold > 0 && $cart) {
            $subTotal = (float) $cart->get_subtotal();
            $progress = min(100, ($subTotal / $threshold) * 100);


            $successText = apply_filters('himuon_flex_cart_free_shipping_success_text', __('Enjoy free shipping today', 'himuon-flex-cart'));

            $remaining = wc_price(max(0, $threshold - (float) $subTotal));

            $remainingText = apply_filters(
                'himuon_flex_cart_free_shipping_remaining_text',
                __('Add %s for free shipping', 'himuon-flex-cart'),
                $remaining,
                $threshold,
                $progress
            );

            $args = [
                'threshold' => $threshold,
                'progress' => $progress,
                'subtotal' => $subTotal,
                'successText' => $successText,
                'remainingText' => $remainingText,
                'remaining' => $remaining
            ];
            return $args;
        }
    }

    public static function header()
    {
        $title = apply_filters('himuon_flex_cart_header_text', __('Your Cart', 'himuon-flex-cart'));

        $cart = self::getCart();
        $counter = $cart ? $cart->get_cart_contents_count() : 0;

        $showCounter = apply_filters(
            'himuon_flex_cart_header_show_counter',
            true,
            $counter,
            $cart
        );

        $counter = apply_filters(
            'himuon_flex_cart_header_counter',
            $counter,
            $cart
        );

        $closeIcon = apply_filters(
            'himuon_flex_cart_header_close_icon',
            '<svg xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"></path>
                    </svg>'
        );

        $args = [
            'title' => $title,
            'showCounter' => $showCounter,
            'counter' => $counter,
            'closeIcon' => $closeIcon
        ];

        return $args;
    }

    public static function cartItems()
    {
        $cart = self::getCart();
        $items = $cart ? $cart->get_cart() : [];
        $views = [];

        foreach ($items as $cartItemKey => $cartItem) {
            $product = isset($cartItem['data']) ? $cartItem['data'] : null;
            if (!$product || !$product->exists() || $cartItem['quantity'] <= 0) {
                continue;
            }

            $view = self::cartItem($cartItemKey, $cartItem, $product);
            if ($view) {
                $views[] = $view;
            }
        }

        return $views;
    }
    public static function cartItem(string $cartItemKey, array $cartItem, WC_Product $product)
    {

        $thumbnail = self::thumbnail($cartItemKey, $cartItem, $product);
        $permalink = self::permalink($cartItemKey, $cartItem, $product);
        $title = self::title($cartItemKey, $cartItem, $product);
        $variation = self::variationData($cartItemKey, $cartItem, $product);
        $subscription = self::hasSubscription($cartItemKey, $cartItem, $product);
        $quantity = self::quantity($cartItemKey, $cartItem, $product);
        $actionHandler = self::actionHandler();
        $actions = self::actions($cartItemKey, $cartItem, $product);
        $price = self::price($cartItemKey, $cartItem, $product);
        $discount = self::discount($cartItemKey, $cartItem, $product);

        $args = [
            'cartItemKey' => $cartItemKey,
            'thumbnail' => $thumbnail,
            'permalink' => $permalink,
            'title' => $title,
            'cartItem' => $cartItem,
            'variation' => $variation,
            'subscription' => $subscription,
            'quantity' => $quantity,
            'actionHandler' => $actionHandler,
            'actions' => $actions,
            'price' => $price,
            'discount' => $discount
        ];
        return $args;
    }

    public static function permalink(string $cartItemKey, array $cartItem, WC_Product $product)
    {
        $showPermalink = apply_filters('himuon_flex_cart_show_permalink', true, $cartItem, $cartItemKey, $product);
        $permalink = '';
        if ($showPermalink && $product->is_visible()) {
            $permalink = $product->get_permalink();
        }
        return $permalink;
    }

    private static function thumbnail(string $cartItemKey, array $cartItem, WC_Product $product)
    {
        $showThumbnail = (bool) apply_filters('himuon_flex_cart_show_thumbnails', true, $cartItem, $cartItemKey, $product);
        $imageSize = (string) apply_filters('himuon_flex_cart_thumbnail_size', 'woocommerce_thumbnail', $product, $cartItem);
        $imageAttrs = (array) apply_filters(
            'himuon_flex_cart_thumbnail_attrs',
            ['class' => 'himuon-cart--image'],
            $product,
            $cartItem
        );
        $thumbnail = '';
        if ($showThumbnail) {
            $thumbnail = $product->get_image($imageSize, $imageAttrs);
            if (empty($thumbnail)) {
                $thumbnail = wc_placeholder_img($imageSize);
            }
        }

        return $thumbnail;
    }

    public static function title(string $cartItemKey, array $cartItem, WC_Product $product)
    {
        $title = $product->get_name();
        if ($product->is_type('variation')) {
            $parentId = $product->get_parent_id();
            $parentProduct = $parentId ? wc_get_product($parentId) : null;
            if ($parentProduct) {
                $title = $parentProduct->get_name();
            }
        }
        return (string) apply_filters(
            'himuon_flex_cart_item_title',
            $title,
            $cartItem,
            $cartItemKey,
            $product
        );
    }

    public static function variationData(string $cartItemKey, array $cartItem, WC_Product $product)
    {
        $variationData = '';
        if ($product->is_type('variation') && $product instanceof WC_Product_Variation) {
            $variationData = wc_get_formatted_variation($cartItem['variation'], true, true, true);
        }
        return (string) apply_filters(
            'himuon_flex_cart_item_variation',
            $variationData,
            $cartItem,
            $cartItemKey,
            $product
        );
    }

    public static function hasSubscription(string $cartItemKey, array $cartItem, WC_Product $product)
    {
        $hasCartSubscriptionData = !empty($cartItem['wcsatt_data']);
        $hasProductSubscriptionSchemes = class_exists('WCS_ATT_Product_Schemes')
            && WCS_ATT_Product_Schemes::has_subscription_schemes($product);

        $hasSubscription = $hasCartSubscriptionData || $hasProductSubscriptionSchemes;

        return (bool) apply_filters(
            'himuon_flex_cart_item_has_subscription',
            $hasSubscription,
            $cartItem,
            $cartItemKey,
            $product
        );
    }

    public static function quantity(string $cartItemKey, array $cartItem, WC_Product $product)
    {
        $qty = (int) $cartItem['quantity'];

        $max = $product->get_max_purchase_quantity();
        $min = $product->get_min_purchase_quantity();

        $minus = apply_filters(
            'himuon_flex_cart_item_quantity_minus_button',
            '-',
        );

        $plus = apply_filters(
            'himuon_flex_cart_item_quantity_plus_button',
            '+',
        );

        return [
            'max' => $max,
            'min' => $min,
            'qty' => $qty,
            'minus' => $minus,
            'plus' => $plus
        ];
    }

    public static function actionHandler()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi-chevron-right bi" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
        </svg>';
        return (string) apply_filters(
            'himuon_flex_cart_item_action_handler',
            $svg
        );
    }

    public static function actions(string $cartItemKey, array $cartItem, WC_Product $product)
    {
        $actions = [
            [
                'id' => 'edit',
                'label' => __('Edit', 'himuon-flex-cart'),
                'class' => 'himuon-cart--action-edit',
            ],
            [
                'id' => 'delete',
                'label' => __('Delete', 'himuon-flex-cart'),
                'class' => 'himuon-cart--action-delete',
            ]
        ];
        return (array) apply_filters(
            'himuon_flex_cart_item_actions',
            $actions,
            $cartItem,
            $cartItemKey,
            $product
        );
    }

    public static function price(string $cartItemKey, array $cartItem, WC_Product $product)
    {
        $qty = max(1, (int) ($cartItem['quantity'] ?? 1));
        return WC()->cart->get_product_subtotal($product, $qty);
    }

    public static function discount(string $cartItemKey, array $cartItem, WC_Product $product)
    {
        $qty = max(1, (int) ($cartItem['quantity'] ?? 1));

        // Coupon/line discount
        $lineSubtotal = (float) ($cartItem['line_subtotal'] ?? 0);
        $lineTotal = (float) ($cartItem['line_total'] ?? 0);
        $lineDiscount = max(0, $lineSubtotal - $lineTotal);

        // Sale discount fallback
        $regular = (float) $product->get_regular_price();
        $active = (float) $product->get_price();
        $saleDiscount = max(0, ($regular - $active) * $qty);

        $amount = max($lineDiscount, $saleDiscount);
        $percent = $regular > 0 ? round((($regular - $active) / $regular) * 100) : 0;

        $discount = [
            'amount' => $amount,
            'formatted' => wc_price($amount),
            'hasDiscount' => $amount > 0,
            'percent' => max(0, $percent),
        ];

        return (array) apply_filters('himuon_flex_cart_item_discount', $discount, $cartItem, $cartItemKey, $product);
    }

    public static function miniCartIcon()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"
             fill="currentColor"
             class="bi bi-basket2-fill"
             viewBox="0 0 16 16">
            <path
                  d="M5.929 1.757a.5.5 0 1 0-.858-.514L2.217 6H.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h.623l1.844 6.456A.75.75 0 0 0 3.69 15h8.622a.75.75 0 0 0 .722-.544L14.877 8h.623a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1.717L10.93 1.243a.5.5 0 1 0-.858.514L12.617 6H3.383zM4 10a1 1 0 0 1 2 0v2a1 1 0 1 1-2 0zm3 0a1 1 0 0 1 2 0v2a1 1 0 1 1-2 0zm4-1a1 1 0 0 1 1 1v2a1 1 0 1 1-2 0v-2a1 1 0 0 1 1-1" />
        </svg>';
        return (string) apply_filters(
            'himuon_flex_cart_mini_cart_icon',
            $svg
        );
    }


}
