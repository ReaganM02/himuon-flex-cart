<?php

namespace Himuon\Flex\Cart\Frontend;

use WCS_ATT_Product_Schemes;
use WC_Product;
use WC_Product_Variation;
use WCS_ATT_Product_Prices;
use WCS_ATT_Product_Price_Filters;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class SubscriptionView
{
    public static function build(WC_Product $product, ?WC_Product_Variation $variation, array $cartItem, string $cartItemKey): array
    {
        $displayProduct = isset($variation) && $variation ? $variation : $product;

        if (!$displayProduct || !class_exists('WCS_ATT_Product_Schemes') || !WCS_ATT_Product_Schemes::has_subscription_schemes($displayProduct)) {
            return [
                'show' => false
            ];
        }

        $schemes = WCS_ATT_Product_Schemes::get_subscription_schemes($displayProduct);
        $selectedSchemeKey = isset($cartItem['wcsatt_data']['active_subscription_scheme'])
            ? $cartItem['wcsatt_data']['active_subscription_scheme']
            : null;
        if (null === $selectedSchemeKey) {
            $selectedSchemeKey = WCS_ATT_Product_Schemes::get_subscription_scheme($displayProduct, 'key');
        }
        if (null === $selectedSchemeKey) {
            $selectedSchemeKey = WCS_ATT_Product_Schemes::get_default_subscription_scheme($displayProduct, 'key');
        }
        $selectedValue = WCS_ATT_Product_Schemes::stringify_subscription_scheme_key($selectedSchemeKey);


        $singlePurchaseAmount = class_exists('WCS_ATT_Product_Prices')
            ? (float) WCS_ATT_Product_Prices::get_price($displayProduct, false, 'view')
            : (float) $displayProduct->get_price();
        $singlePurchasePrice = wc_price(
            wc_get_price_to_display($displayProduct, ['price' => $singlePurchaseAmount])
        );

        $options = [];

        $hasPriceFilterClass = class_exists('WCS_ATT_Product_Price_Filters');
        if ($hasPriceFilterClass) {
            WCS_ATT_Product_Price_Filters::add('price');
        }

        try {
            foreach ($schemes as $schemeKey => $scheme) {
                $interval = (int) $scheme->get_interval();
                $period = (string) $scheme->get_period();

                $periodLabel = function_exists('wcs_get_subscription_period_strings')
                    ? wcs_get_subscription_period_strings($interval, $period) // week/weeks, month/months, etc.
                    : $period;

                if (!function_exists('wcs_get_subscription_period_strings') && $interval > 1 && !str_ends_with($periodLabel, 's')) {
                    $periodLabel .= 's';
                }

                $title = sprintf(__('Every %s', 'himuon-flex-cart'), $periodLabel);

                $title = (string) apply_filters(
                    'himuon_flex_cart_subscription_option_title',
                    $title,
                    $scheme,
                    $schemeKey,
                    $displayProduct,
                    $product,
                    $variation,
                    $cartItem,
                    $cartItemKey
                );

                $price = class_exists('WCS_ATT_Product_Prices')
                    ? (float) WCS_ATT_Product_Prices::get_price($displayProduct, $schemeKey, 'view')
                    : (float) $displayProduct->get_price();

                $priceHtml = wc_price(wc_get_price_to_display($displayProduct, ['price' => $price]));

                $discount = class_exists('WCS_ATT_Product_Prices')
                    ? WCS_ATT_Product_Prices::get_formatted_discount($displayProduct, $scheme) // e.g. "10%"
                    : '';

                $optionValue = WCS_ATT_Product_Schemes::stringify_subscription_scheme_key($schemeKey);

                $discountText = $discount ? sprintf(__('(%s off)', 'himuon-flex-cart'), $discount) : '';

                $options[] = [
                    'value' => $optionValue,
                    'isChecked' => $selectedValue === $optionValue,
                    'title' => $title,
                    'detail' => '',
                    'price' => $priceHtml,
                    'discount' => $discountText,
                ];
            }
        } finally {
            if ($hasPriceFilterClass) {
                WCS_ATT_Product_Price_Filters::remove('price');
            }
        }

        return [
            'show' => true,
            'purchaseOptionsLabel' => self::purchaseOptionsLabel(),
            'singlePurchaseLabel' => self::singlePurchaseLabel(),
            'singlePurchasePrice' => $singlePurchasePrice,
            'selectedValue' => $selectedValue,
            'options' => $options,
        ];
    }

    public static function purchaseOptionsLabel()
    {
        return (string) apply_filters(
            'himuon_flex_cart_subscription_purchase_option_label',
            __('Purchase options', 'himuon-flex-cart')
        );
    }

    public static function singlePurchaseLabel()
    {
        return (string) apply_filters(
            'himuon_flex_cart_subscription_single_purchase_label',
            __('Single purchase', 'himuon-flex-cart')
        );
    }

    private static function log($data)
    {
        error_log(print_r($data, true));
    }
}
