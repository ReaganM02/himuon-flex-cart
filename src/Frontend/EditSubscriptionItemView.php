<?php

namespace Himuon\Flex\Cart\Frontend;

use Himuon\Flex\Cart\Frontend\CartItemView;
use Himuon\Flex\Cart\Frontend\EditCartItemView;
use Himuon\Flex\Cart\Frontend\SubscriptionView;
use WC_Product;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class EditSubscriptionItemView
{
    public static function build(array $cartItem, string $cartItemKey)
    {
        if (empty($cartItem['data']) || !($cartItem['data'] instanceof WC_Product)) {
            return false;
        }

        $product = $cartItem['data'];
        if ($product->is_type('variation')) {
            return false;
        }

        $subscriptionData = SubscriptionView::build($product, null, $cartItem, $cartItemKey);
        if (empty($subscriptionData['show'])) {
            return false;
        }

        return [
            'product' => $product,
            'cartItem' => $cartItem,
            'cartItemKey' => $cartItemKey,
            'title' => CartItemView::title($cartItemKey, $cartItem, $product),
            'permalink' => CartItemView::permalink($cartItemKey, $cartItem, $product),
            'viewLabel' => EditCartItemView::viewProductLabel($cartItem, $cartItemKey, $product),
            'updateLabel' => EditCartItemView::updateCartLabel($cartItem, $cartItemKey, $product),
            'closeEditPanel' => EditCartItemView::closeEditPanel($cartItem, $cartItemKey, $product),
            'subscriptionData' => $subscriptionData,
        ];
    }
}
