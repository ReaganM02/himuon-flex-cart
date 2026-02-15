<?php
namespace Himuon\Flex\Cart\Frontend;


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SideCartView
{
    /**
     * Request-level cache for computed discount total.
     *
     * @var float|null
     */
    private static $discountTotalCache = null;

    private static function getCart()
    {
        if (!function_exists('WC') || !WC()->cart) {
            return null;
        }
        return WC()->cart;
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

    public static function getSubtotal()
    {
        $cart = self::getCart();
        if ($cart) {
            return $cart->get_cart_subtotal();
        }
        return '';
    }

    public static function getPayableTotalRaw(): float
    {
        $cart = self::getCart();
        if (!$cart) {
            return 0.0;
        }

        $total = (float) $cart->get_cart_contents_total();

        if (method_exists($cart, 'display_prices_including_tax') && $cart->display_prices_including_tax()) {
            $total += (float) $cart->get_cart_contents_tax();
        }

        return (float) apply_filters(
            'himuon_flex_cart_footer_payable_total_raw',
            $total,
            $cart
        );
    }

    public static function getPayableTotal(): string
    {
        return (string) apply_filters(
            'himuon_flex_cart_footer_payable_total',
            wc_price(self::getPayableTotalRaw()),
            self::getCart()
        );
    }

    public static function subtotalLabel()
    {
        return (string) apply_filters(
            'himuon_flex_cart_footer_subtotal_label',
            __('Subtotal', 'himuon-flex-cart'),
            self::getCart()
        );
    }

    public static function payableTotalLabel()
    {
        return (string) apply_filters(
            'himuon_flex_cart_footer_payable_total_label',
            __('Total', 'himuon-flex-cart'),
            self::getCart()
        );
    }

    public static function checkoutLabel()
    {
        return (string) apply_filters(
            'himuon_flex_cart_footer_checkout_button_label',
            __('Checkout', 'himuon-flex-cart'),
            self::getCart()
        );
    }

    public static function getDiscountTotalRaw(): float
    {
        if (null !== self::$discountTotalCache) {
            return self::$discountTotalCache;
        }

        $cart = self::getCart();
        if (!$cart) {
            self::$discountTotalCache = 0.0;
            return self::$discountTotalCache;
        }

        $discountTotal = 0.0;

        foreach ($cart->get_cart() as $cartItem) {
            $product = isset($cartItem['data']) ? $cartItem['data'] : null;
            if (!$product || !($product instanceof \WC_Product)) {
                continue;
            }

            $lineSubtotal = (float) ($cartItem['line_subtotal'] ?? 0);
            $lineTotal = (float) ($cartItem['line_total'] ?? 0);
            $lineDiscount = max(0, $lineSubtotal - $lineTotal);

            if ($lineDiscount > 0) {
                $discountTotal += $lineDiscount;
                continue;
            }

            $qty = max(1, (int) ($cartItem['quantity'] ?? 1));
            $regular = (float) $product->get_regular_price();
            $active = (float) $product->get_price();
            $saleDiscount = max(0, ($regular - $active) * $qty);

            $discountTotal += $saleDiscount;
        }

        self::$discountTotalCache = (float) apply_filters(
            'himuon_flex_cart_footer_discount_total_raw',
            $discountTotal,
            $cart
        );

        return self::$discountTotalCache;
    }

    public static function getDiscountTotal(): string
    {
        return (string) apply_filters(
            'himuon_flex_cart_footer_discount_total',
            wc_price(self::getDiscountTotalRaw()),
            self::getCart()
        );
    }

    public static function hasDiscountTotal(): bool
    {
        return self::getDiscountTotalRaw() > 0;
    }

    public static function discountLabel(): string
    {
        return (string) apply_filters(
            'himuon_flex_cart_footer_discount_label',
            __('Discount', 'himuon-flex-cart'),
            self::getCart()
        );
    }

}
