<?php

namespace Himuon\Flex\Cart;

use Himuon\Flex\Cart\Frontend\EditCartItemView;
use Himuon\Flex\Cart\Subscription;
use Himuon\Flex\Cart\Helper;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Variation
{
    public static function getForm($cartItem, $cartItemKey)
    {
        if (!$cartItem) {
            return false;
        }

        $viewData = EditCartItemView::build($cartItem, $cartItemKey);
        if (!$viewData) {
            return false;
        }
        $parent = $viewData['product'];

        $previousProduct = isset($GLOBALS['product']) ? $GLOBALS['product'] : null;
        $GLOBALS['product'] = $parent;

        $removedSubscriptionOptionsLayout = Subscription::removeSubscriptionOptionsLayout();
        $removedPriceFilters = Subscription::removeSubscriptionPriceFilters();

        self::addVariationDataFilters();
        Subscription::renderOptions($cartItem, $cartItemKey);
        $viewData['available_variations'] = $parent->get_available_variations();

        remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);
        try {
            ob_start();
            Helper::template('edit-cart-item.php', $viewData);
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

            self::removeVariationDataFilters();
            Subscription::removeRenderOptionsHook();

        }

        return $form;
    }

    public static function addVariationDataFilters()
    {
        add_filter('woocommerce_available_variation', [self::class, 'variationData'], 10, 3);
    }

    public static function removeVariationDataFilters()
    {
        remove_filter('woocommerce_available_variation', [self::class, 'variationData'], 10);
    }

    public static function variationData($variationData, $product, $variation)
    {
        // Remove description
        $variationData['variation_description'] = '';

        // Ensure price is always visible in edit panel, even when WooCommerce returns empty
        $priceHtml = isset($variationData['price_html']) ? (string) $variationData['price_html'] : '';
        if ('' === trim(wp_strip_all_tags($priceHtml))) {
            $fallbackPriceHtml = (string) $variation->get_price_html();

            if ('' === trim(wp_strip_all_tags($fallbackPriceHtml))) {
                $displayPrice = wc_get_price_to_display($variation);
                $fallbackPriceHtml = wc_price($displayPrice);
            }

            $variationData['price_html'] = $fallbackPriceHtml;
        }

        return $variationData;
    }


}
