<?php

namespace Himuon\Flex\Cart\Frontend;


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class CouponView
{
    public static function couponHandlerLabelLeft()
    {
        return (string) apply_filters(
            'himuon_flex_cart_coupon_handler_left',
            __('Have a voucher?', 'himuon-flex-cart')
        );
    }

    public static function couponHandlerLabelRight()
    {
        return (string) apply_filters(
            'himuon_flex_cart_coupon_handler_right',
            __('Select', 'himuon-flex-cart')
        );
    }

    public static function couponTitle()
    {
        return (string) apply_filters(
            'himuon_flex_cart_coupon_title',
            __('Apply Voucher', 'himuon-flex-cart')
        );
    }

    public static function appliedCouponsLabel()
    {
        return (string) apply_filters(
            'himuon_flex_cart_applied_coupons_label',
            __('Coupons', 'himuon-flex-cart')
        );
    }

    public static function closeCouponPanelIcon()
    {
        return (string) apply_filters(
            'himuon_flex_cart_coupon_close_panel_icon',
            '<svg xmlns="http://www.w3.org/2000/svg"
             fill="none"
             viewBox="0 0 24 24"
             stroke-width="1.5"
             stroke="currentColor"
             aria-hidden="true">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 18L18 6M6 6l12 12" />
        </svg>'
        );
    }

    public static function applyCouponLabel()
    {
        return (string) apply_filters(
            'himuon_flex_cart_apply_coupon_label',
            __('Apply', 'himuon-flex-cart')
        );
    }

    public static function removeCouponIcon()
    {
        return (string) apply_filters(
            'himuon_flex_cart_remove_coupon_icon',
            '<svg xmlns="http://www.w3.org/2000/svg"
             fill="none"
             viewBox="0 0 24 24"
             stroke-width="1.5"
             stroke="currentColor"
             aria-hidden="true">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 18L18 6M6 6l12 12" />
        </svg>'
        );
    }
}
