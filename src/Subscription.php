<?php

namespace Himuon\Flex\Cart;

use WCS_ATT_Display_Product;
use WCS_ATT_Product_Price_Filters;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Subscription
{
    public static $removePriceFilter = false;
    public static function removeLayout()
    {
        if (class_exists('WCS_ATT_Display_Product')) {
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
        }
    }

    public static function addLayout()
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
        }
    }

    public static function removePriceFilter()
    {
        if (class_exists('WCS_ATT_Product_Price_Filters')) {
            WCS_ATT_Product_Price_Filters::remove('price');
            WCS_ATT_Product_Price_Filters::remove('price_html');
            self::$removePriceFilter = true;
        }
    }

    public static function addPriceFilter()
    {
        if (self::$removePriceFilter) {
            WCS_ATT_Product_Price_Filters::add('price');
            WCS_ATT_Product_Price_Filters::add('price_html');
        }
    }

    public static function renderOptions()
    {
        add_filter('woocommerce_available_variation', [self::class, 'filterVariationData'], 0, 3);
    }

    public static function filterVariationData($variationData, $product, $variation)
    {
        $existingPriceHTML = isset($variationData['price_html']) ? $variationData['price_html'] : '';
        $variationData['price_html'] = $existingPriceHTML . self::options($product, $variation);
        return $variationData;
    }

    private static function options($product, $variation)
    {
        ob_start();
        require HIMUON_FLEX_CART_PATH . 'templates/subscription.php';
        return ob_get_clean();
    }

    public static function removeRenderOptionsHook()
    {
        remove_filter('woocommerce_available_variation', [self::class, 'filterVariationData'], 10);
    }

}