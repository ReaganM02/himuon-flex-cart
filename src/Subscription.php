<?php

namespace Himuon\Flex\Cart;

use Himuon\Flex\Cart\Frontend\SubscriptionView;
use Himuon\Flex\Cart\Helper;
use WCS_ATT_Product_Price_Filters;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Subscription
{
    private static $renderContext = [];

    public static function removeSubscriptionPriceFilters()
    {
        if (class_exists('WCS_ATT_Product_Price_Filters')) {
            WCS_ATT_Product_Price_Filters::remove('price');
            WCS_ATT_Product_Price_Filters::remove('price_html');
            return true;
        }
        return false;
    }

    public static function addSubscriptionPriceFilters()
    {
        if (class_exists('WCS_ATT_Product_Price_Filters')) {
            WCS_ATT_Product_Price_Filters::add('price');
            WCS_ATT_Product_Price_Filters::add('price_html');
            return true;
        }
        return false;
    }

    public static function removeSubscriptionOptionsLayout()
    {
        if (!class_exists('WCS_ATT_Display_Product')) {
            return false;
        }
        remove_action(
            'woocommerce_before_add_to_cart_button',
            ['WCS_ATT_Display_Product', 'show_subscription_options'],
            100
        );

        remove_filter(
            'woocommerce_available_variation',
            ['WCS_ATT_Display_Product', 'add_subscription_options_to_variation_data'],
            1
        );

        return true;
    }

    public static function addSubscriptionOptionsLayout()
    {
        if (class_exists('WCS_ATT_Display_Product')) {
            add_action(
                'woocommerce_before_add_to_cart_button',
                ['WCS_ATT_Display_Product', 'show_subscription_options'],
                100
            );
            add_filter(
                'woocommerce_available_variation',
                ['WCS_ATT_Display_Product', 'add_subscription_options_to_variation_data'],
                1,
                3
            );
            return true;
        }
        return false;
    }

    public static function renderOptions($cartItem, $cartItemKey)
    {
        self::$renderContext = [
            'cartItem' => is_array($cartItem) ? $cartItem : [],
            'cartItemKey' => is_string($cartItemKey) ? $cartItemKey : '',
        ];

        if (!has_filter('woocommerce_available_variation', [self::class, 'filterVariationData'])) {
            add_filter('woocommerce_available_variation', [self::class, 'filterVariationData'], 10, 3);
        }
    }

    public static function filterVariationData($variationData, $product, $variation)
    {
        $existingPriceHTML = isset($variationData['price_html']) ? $variationData['price_html'] : '';
        $cartItem = isset(self::$renderContext['cartItem']) && is_array(self::$renderContext['cartItem'])
            ? self::$renderContext['cartItem']
            : [];
        $cartItemKey = isset(self::$renderContext['cartItemKey']) ? (string) self::$renderContext['cartItemKey'] : '';
        $variationData['price_html'] = $existingPriceHTML . self::options($product, $variation, $cartItem, $cartItemKey);
        return $variationData;
    }

    private static function options($product, $variation, $cartItem, $cartItemKey)
    {
        $viewData = SubscriptionView::build($product, $variation, $cartItem, $cartItemKey);
        if (empty($viewData['show'])) {
            return '';
        }

        ob_start();
        Helper::template('subscription-options.php', $viewData);
        return ob_get_clean();
    }

    public static function removeRenderOptionsHook()
    {
        remove_filter('woocommerce_available_variation', [self::class, 'filterVariationData'], 10);
        self::$renderContext = [];
    }
}
