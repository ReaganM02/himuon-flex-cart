<?php

namespace Himuon\Flex\Cart\Frontend;

use Himuon\Flex\Cart\Subscription;
use Himuon\Flex\Cart\Variation;
use WC_Product_Variable;
use WCS_ATT_Product_Schemes;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}



final class SideCart
{
    public function register()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_footer', [$this, 'content'], 100);
        add_filter('woocommerce_add_to_cart_fragments', [$this, 'addFragments']);

        add_action('wp_ajax_himuon_update_cart_item', [$this, 'updateCartItem']);
        add_action('wp_ajax_nopriv_himuon_update_cart_item', [$this, 'updateCartItem']);
        add_action('wc_ajax_himuon_update_cart_item', [$this, 'updateCartItem']);

        add_action('wp_ajax_himuon_update_cart_item_variation', [$this, 'updateCartItemVariation']);
        add_action('wp_ajax_nopriv_himuon_update_cart_item_variation', [$this, 'updateCartItemVariation']);
        add_action('wc_ajax_himuon_update_cart_item_variation', [$this, 'updateCartItemVariation']);

        add_action('wp_ajax_himuon_delete_cart_item', [$this, 'deleteCartItem']);
        add_action('wp_ajax_nopriv_himuon_delete_cart_item', [$this, 'deleteCartItem']);
        add_action('wc_ajax_himuon_delete_cart_item', [$this, 'deleteCartItem']);

        add_action('wp_ajax_himuon_cart_item_edit', [$this, 'cartItemEdit']);
        add_action('wp_ajax_nopriv_himuon_cart_item_edit', [$this, 'cartItemEdit']);
        add_action('wc_ajax_himuon_cart_item_edit', [$this, 'cartItemEdit']);

    }

    public function enqueueScripts()
    {
        wp_enqueue_style(
            'himuon-flex-cart',
            HIMUON_FLEX_CART_URL . 'assets/css/himuon-flex-cart.css',
            [],
            HIMUON_FLEX_CART_VERSION
        );

        wp_enqueue_script(
            'himuon-flex-cart',
            HIMUON_FLEX_CART_URL . 'assets/js/himuon-flex-cart.js',
            ['jquery', 'wc-cart-fragments'],
            HIMUON_FLEX_CART_VERSION,
            [
                'strategy' => 'defer',
                'in_footer' => true
            ]
        );

        wp_enqueue_script('wc-add-to-cart-variation');

        wp_localize_script('himuon-flex-cart', 'himuonFlexCart', [
            'nonce' => wp_create_nonce('himuon_flex_cart'),
            'url' => admin_url('admin-ajax.php')
        ]);
    }

    public function cartItemEdit()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        $cartItemKey = isset($_POST['cartItemKey']) ? wc_clean(wp_unslash($_POST['cartItemKey'])) : '';

        $cartItem = WC()->cart->get_cart_item($cartItemKey);

        $form = Variation::getForm($cartItem, $cartItemKey);

        if (!$form) {
            wp_send_json_error(__('Invalid parent product', 'himuon-flex-cart'));
        }

        $result = [
            'form' => $form,
            'attributes' => $cartItem['variation']
        ];
        wp_send_json_success($result);
    }

    public function content()
    {
        if (!function_exists('WC') || !WC()->cart) {
            return '';
        }

        $items = WC()->cart->get_cart();
        ob_start();
        require_once HIMUON_FLEX_CART_PATH . 'templates/wrapper.php';
        echo ob_get_clean();
    }

    public function addFragments($fragments)
    {
        ob_start();
        $items = WC()->cart ? WC()->cart->get_cart() : [];
        require HIMUON_FLEX_CART_PATH . 'templates/side-cart.php';
        $fragments['#himuon-side-cart'] = ob_get_clean();

        ob_start();
        require HIMUON_FLEX_CART_PATH . 'templates/mini-cart.php';
        $fragments['#himuon-mini-cart'] = ob_get_clean();

        return $fragments;
    }

    public function updateCartItem()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => 'Cart not available.'], 400);
        }

        $cartItemKey = isset($_POST['cartItemKey']) ? wc_clean(wp_unslash($_POST['cartItemKey'])) : '';
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

        if ('' === $cartItemKey || $quantity < 0) {
            wp_send_json_error(['message' => 'Invalid cart data.'], 400);
        }

        WC()->cart->set_quantity($cartItemKey, $quantity, true);
        WC()->cart->calculate_totals();

        $fragments = apply_filters('woocommerce_add_to_cart_fragments', []);
        $cartHash = WC()->cart->get_cart_hash();

        wp_send_json_success([
            'fragments' => $fragments,
            'cart_hash' => $cartHash,
        ]);
    }

    public function updateCartItemVariation()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => 'Cart not available.'], 400);
        }

        $cartItemKey = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';

        $cart = WC()->cart->get_cart();
        $cartItem = isset($cart[$cartItemKey]) ? $cart[$cartItemKey] : null;

        if (!$cartItem) {
            wp_send_json_error(__('Invalid Cart Item', 'himuon-flex-cart'));
        }

        $product = $cartItem['data'];

        if (!$product) {
            wp_send_json_error(['message' => __('Product not found', 'himuon-flex-cart')]);
        }

        $variation = [];
        $parent = $product;

        if ($product->is_type('variation')) {
            $parentId = $product->get_parent_id();
            $parent = $parentId ? wc_get_product($parentId) : $product;
        }

        $attributes = $parent->get_variation_attributes();

        foreach ($attributes as $attributeName => $options) {
            $key = sanitize_title($attributeName);
            $postKey = 'attribute_' . $key;

            // Accept if client sent attribute_pa_color or pa_color
            $raw = null;
            if (isset($_POST[$postKey])) {
                $raw = $_POST[$postKey];
            } elseif (isset($_POST[$key])) {
                $raw = $_POST[$key];
                $postKey = $key; // keep original key if you want
            }

            if ($raw === null) {
                continue;
            }

            $value = wc_clean(wp_unslash($raw));
            if ($value !== '') {
                $variation['attribute_' . $key] = $value; // normalize output key
            }
        }

        $existing = $cartItem['variation'] ?? [];
        $new = $variation ?? [];

        $existingSubscriptionScheme = isset($cartItem['wcsatt_data']['active_subscription_scheme'])
            ? $cartItem['wcsatt_data']['active_subscription_scheme']
            : null;

        $newSubscriptionScheme = $existingSubscriptionScheme;
        $subscriptionSelectionPosted = false;

        if (isset($_POST['convert_to_sub'])) {
            $subscriptionSelectionPosted = true;
            $postedScheme = wc_clean(wp_unslash($_POST['convert_to_sub']));
            if (class_exists('WCS_ATT_Product_Schemes')) {
                $newSubscriptionScheme = WCS_ATT_Product_Schemes::parse_subscription_scheme_key($postedScheme);
            } else {
                $newSubscriptionScheme = '' !== $postedScheme ? (string) $postedScheme : false;
            }
        }

        ksort($existing);
        ksort($new);

        $existingSubscriptionValue = (false === $existingSubscriptionScheme || null === $existingSubscriptionScheme)
            ? '0'
            : (string) $existingSubscriptionScheme;
        $newSubscriptionValue = (false === $newSubscriptionScheme || null === $newSubscriptionScheme)
            ? '0'
            : (string) $newSubscriptionScheme;

        if ($existing === $new && $existingSubscriptionValue === $newSubscriptionValue) {
            wp_send_json_success([
                'no_change' => true,
                'variation' => $existing,
            ]);
        }

        $productId = (int) $cartItem['product_id'];
        $variationId = 0;
        if ($parent instanceof WC_Product_Variable) {
            $dataStore = \WC_Data_Store::load('product');
            if ($dataStore && is_callable([$dataStore, 'find_matching_product_variation'])) {
                $variationId = (int) $dataStore->find_matching_product_variation($parent, $variation);
            } else {
                // Backward compatibility with older WooCommerce versions.
                $variationId = (int) $parent->get_matching_variation($variation);
            }
        }

        if (!$variationId) {
            wp_send_json_error(['message' => 'Invalid variation.'], 400);
        }

        $keys = array_keys($cart);
        $oldIndex = array_search($cartItemKey, $keys, true);

        $oldProductId = (int) $cartItem['product_id'];
        $oldVariationId = (int) $cartItem['variation_id'];
        $quantity = (int) $cartItem['quantity'];
        $oldCartItemData = $cartItem['cart_item_data'] ?? [];
        if (isset($cartItem['wcsatt_data'])) {
            $oldCartItemData['wcsatt_data'] = $cartItem['wcsatt_data'];
        }

        $newCartItemData = $oldCartItemData;
        if ($subscriptionSelectionPosted) {
            $wcsattData = isset($newCartItemData['wcsatt_data']) && is_array($newCartItemData['wcsatt_data'])
                ? $newCartItemData['wcsatt_data']
                : [];
            $wcsattData['active_subscription_scheme'] = $newSubscriptionScheme;
            $newCartItemData['wcsatt_data'] = $wcsattData;
        }

        WC()->cart->remove_cart_item($cartItemKey);
        $newKey = WC()->cart->add_to_cart($productId, $quantity, $variationId, $variation, $newCartItemData);

        if (!$newKey) {
            // Restore original item if update fails.
            WC()->cart->add_to_cart(
                $oldProductId,
                $quantity,
                $oldVariationId,
                $existing,
                $oldCartItemData
            );
            wp_send_json_error(['message' => 'Unable to update item.'], 500);
        }

        if ($newKey && $oldIndex !== false) {
            // rebuild cart contents in original order
            $newCart = [];
            foreach ($keys as $key) {
                if ($key === $cartItemKey) {
                    $newCart[$newKey] = WC()->cart->get_cart_item($newKey);
                } elseif ($key !== $newKey && isset($cart[$key])) {
                    $newCart[$key] = $cart[$key];
                }
            }
            WC()->cart->cart_contents = $newCart;
        }

        WC()->cart->calculate_totals();

        $fragments = apply_filters('woocommerce_add_to_cart_fragments', []);
        $cartHash = WC()->cart->get_cart_hash();

        wp_send_json_success([
            'cart_item_key' => $newKey,
            'fragments' => $fragments,
            'cart_hash' => $cartHash,
        ]);
    }

    public function deleteCartItem()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => 'Cart not available.'], 400);
        }

        $cartItemKey = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';

        if ('' === $cartItemKey) {
            wp_send_json_error(['message' => 'Invalid cart data.'], 400);
        }

        $cart = WC()->cart->get_cart();
        if (!isset($cart[$cartItemKey])) {
            wp_send_json_error(['message' => 'Cart item not found.'], 404);
        }

        WC()->cart->remove_cart_item($cartItemKey);
        WC()->cart->calculate_totals();

        $fragments = apply_filters('woocommerce_add_to_cart_fragments', []);
        $cartHash = WC()->cart->get_cart_hash();

        wp_send_json_success([
            'fragments' => $fragments,
            'cart_hash' => $cartHash,
        ]);
    }

}
