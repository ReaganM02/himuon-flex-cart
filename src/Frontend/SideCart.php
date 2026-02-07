<?php

namespace Himuon\Flex\Cart\Frontend;

use Himuon\Flex\Cart\Subscription;
use WCS_ATT_Product_Price_Filters;

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


        add_action('wp_ajax_himuon_render_variation', [$this, 'renderVariation']);
        add_action('wp_ajax_nopriv_himuon_render_variation', [$this, 'renderVariation']);
        add_action('wc_ajax_himuon_render_variation', [$this, 'renderVariation']);


        add_action('wp_ajax_himuon_update_cart_item_variation', [$this, 'updateCartItemVariation']);
        add_action('wp_ajax_nopriv_himuon_update_cart_item_variation', [$this, 'updateCartItemVariation']);
        add_action('wc_ajax_himuon_update_cart_item_variation', [$this, 'updateCartItemVariation']);

        add_action('wp_ajax_himuon_delete_cart_item', [$this, 'deleteCartItem']);
        add_action('wp_ajax_nopriv_himuon_delete_cart_item', [$this, 'deleteCartItem']);
        add_action('wc_ajax_himuon_delete_cart_item', [$this, 'deleteCartItem']);
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
            true
        );

        wp_enqueue_script('wc-add-to-cart-variation');

        wp_localize_script('himuon-flex-cart', 'himuonFlexCart', [
            'nonce' => wp_create_nonce('himuon_flex_cart'),
            'url' => admin_url('admin-ajax.php')
        ]);
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

    public function renderVariation()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        if (!function_exists('WC')) {
            wp_send_json_error(['message' => 'WooCommerce not available.'], 400);
        }

        // Quantity 
        $quantity = isset($_POST['quantity']) ? absint(wc_clean(wp_unslash($_POST['quantity']))) : 1;

        // Parent product ID
        $productID = isset($_POST['productId']) ? absint(wc_clean(wp_unslash($_POST['productId']))) : '';
        $product = wc_get_product($productID);

        if (!$product || !$product->is_type('variable')) {
            wp_send_json_error(['message' => 'Invalid Product.'], 400);
        }

        $previousProduct = null;
        if (isset($GLOBALS['product'])) {
            $previousProduct = $GLOBALS['product'];
        }

        $GLOBALS['product'] = $product;

        remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);

        Subscription::removeLayout();

        add_action('woocommerce_after_variations_form', function () use ($productID, $quantity) {
            echo '<input type="hidden" name="quantity" value="' . esc_attr($quantity) . '">';
            echo '<input type="hidden" name="product_id" value="' . esc_attr($productID) . '">';
            echo '<input type="hidden" name="variation_id" value="0">'; // JS will set it
        });

        ob_start();

        Subscription::$removePriceFilter = false;
        Subscription::removePriceFilter();


        $attributes = $product->get_variation_attributes();
        $availableVariations = $product->get_available_variations();

        require_once HIMUON_FLEX_CART_PATH . 'templates/variation.php';

        $output = ob_get_clean();

        if (null !== $previousProduct) {
            $GLOBALS['product'] = $previousProduct;
        } else {
            unset($GLOBALS['product']);
        }

        add_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);

        Subscription::addLayout();
        if (Subscription::$removePriceFilter) {
            Subscription::addPriceFilter();
        }

        wp_send_json_success(['html' => $output]);
    }

    public function updateCartItemVariation()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => 'Cart not available.'], 400);
        }

        $cartItemKey = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
        $productId = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $variationId = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0;
        $quantity = isset($_POST['quantity']) ? max(1, (int) $_POST['quantity']) : 1;


        $product = wc_get_product($productId);

        if (!$product) {
            wp_send_json_error(['message' => __('Product not found', 'himuon-flex-cart')]);
        }

        $variation = [];
        $attributes = $product->get_variation_attributes();
        foreach ($attributes as $attributeName => $options) {
            $postKey = 'attribute_' . sanitize_title($attributeName);
            if (!isset($_POST[$postKey])) {
                continue;
            }
            $value = wc_clean(wp_unslash($_POST[$postKey]));
            if ($value !== '') {
                $variation[$postKey] = $value;
            }
        }

        if (!$cartItemKey || !$productId || !$variationId || empty($variation)) {
            wp_send_json_error(['message' => 'Invalid data.'], 400);
        }

        $cart = WC()->cart->get_cart();
        if (!isset($cart[$cartItemKey])) {
            wp_send_json_error(['message' => 'Cart item not found.'], 404);
        }

        $cartItem = $cart[$cartItemKey];
        $keys = array_keys($cart);
        $oldIndex = array_search($cartItemKey, $keys, true);

        if ((int) $cartItem['product_id'] !== $productId) {
            wp_send_json_error(['message' => 'Product mismatch.'], 400);
        }

        $variationProduct = wc_get_product($variationId);
        if (!$variationProduct || (int) $variationProduct->get_parent_id() !== $productId) {
            wp_send_json_error(['message' => 'Invalid variation.'], 400);
        }

        if ($product && $product->is_type('variable')) {
            $matchedVariationId = $product->get_matching_variation($variation);
            if ($matchedVariationId && (int) $matchedVariationId !== $variationId) {
                wp_send_json_error(['message' => 'Variation mismatch.'], 400);
            }
        }

        $existingProductId = (int) $cartItem['product_id'];
        $existingVariationId = (int) $cartItem['variation_id'];
        $existingQty = (int) $cartItem['quantity'];
        $existingVariation = isset($cartItem['variation']) ? (array) $cartItem['variation'] : [];
        $existingWcsattData = isset($cartItem['wcsatt_data']) ? (array) $cartItem['wcsatt_data'] : [];


        // Normalize for comparison
        ksort($existingVariation);
        ksort($variation);

        if (
            $existingProductId === $productId &&
            $existingVariationId === $variationId &&
            $existingQty === $quantity &&
            $existingVariation === $variation
        ) {
            $fragments = apply_filters('woocommerce_add_to_cart_fragments', []);
            $cartHash = WC()->cart->get_cart_hash();
            wp_send_json_success([
                'cart_item_key' => $cartItemKey,
                'fragments' => $fragments,
                'cart_hash' => $cartHash,
                'no_change' => true,
            ]);
        }


        WC()->cart->remove_cart_item($cartItemKey);

        $cartItemData = [];
        if (!empty($existingWcsattData) && array_key_exists('active_subscription_scheme', $existingWcsattData)) {
            $cartItemData['wcsatt_data'] = [
                'active_subscription_scheme' => $existingWcsattData['active_subscription_scheme'],
            ];
        }

        $newKey = WC()->cart->add_to_cart($productId, $quantity, $variationId, $variation, $cartItemData);

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
