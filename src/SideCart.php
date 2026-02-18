<?php

namespace Himuon\Flex\Cart;

use Himuon\Flex\Cart\Frontend\EditSubscriptionItemView;
use Himuon\Flex\Cart\Helper;
use Himuon\Flex\Cart\Variation;
use WCS_ATT_Cart;
use WC_Product_Variable;
use WCS_ATT_Product_Schemes;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}



final class SideCart
{
    private function shouldShowMiniCart(): bool
    {
        $cart = (function_exists('WC') && WC()->cart) ? WC()->cart : null;
        return (bool) apply_filters('himuon_flex_cart_show_mini_cart', true, $cart);
    }

    private function getRequestString(string $key): string
    {
        $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);
        if (null === $value || false === $value) {
            return '';
        }

        return wc_clean(wp_unslash((string) $value));
    }

    private function getRequestNullableString(string $key): ?string
    {
        $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);
        if (null === $value || false === $value) {
            return null;
        }

        return wc_clean(wp_unslash((string) $value));
    }

    private function getRequestInt(string $key, int $default = 0): int
    {
        $value = $this->getRequestNullableString($key);
        if (null === $value || '' === $value) {
            return $default;
        }

        return (int) $value;
    }

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

        add_action('wp_ajax_himuon_update_cart_item_subscription', [$this, 'updateCartItemSubscription']);
        add_action('wp_ajax_nopriv_himuon_update_cart_item_subscription', [$this, 'updateCartItemSubscription']);
        add_action('wc_ajax_himuon_update_cart_item_subscription', [$this, 'updateCartItemSubscription']);

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
        $themeCss = $this->buildThemeVariablesCss();
        if ('' !== $themeCss) {
            wp_add_inline_style('himuon-flex-cart', $themeCss);
        }

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

        wp_enqueue_script(
            'himuon-flex-cart-add-to-cart',
            HIMUON_FLEX_CART_URL . 'assets/js/himuon-flex-cart-add-to-cart.js',
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

    private function buildThemeVariablesCss()
    {
        $colors = apply_filters('himuon_flex_cart_theme_colors', []);
        if (!is_array($colors) || [] === $colors) {
            return '';
        }

        $css = '';
        foreach ($colors as $name => $value) {
            $name = sanitize_key((string) $name);
            if ('' === $name) {
                continue;
            }

            $value = sanitize_hex_color((string) $value);
            if (null === $value) {
                continue;
            }

            $css .= '--himuon-cart--' . $name . ':' . $value . ';';
        }

        return '' === $css ? '' : '.himuon-flex-cart-plugin{' . $css . '}';
    }

    public function cartItemEdit()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        $cartItemKey = $this->getRequestString('cartItemKey');

        $cartItem = WC()->cart->get_cart_item($cartItemKey);

        $form = Variation::getForm($cartItem, $cartItemKey);
        $attributes = isset($cartItem['variation']) && is_array($cartItem['variation']) ? $cartItem['variation'] : [];

        if (!$form) {
            $form = $this->renderSimpleSubscriptionForm($cartItem, $cartItemKey);
            $attributes = [];
        }

        if (!$form) {
            wp_send_json_error(__('Invalid parent product', 'himuon-flex-cart'));
        }

        $result = [
            'form' => $form,
            'attributes' => $attributes
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
        Helper::template('wrapper.php');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template output is escaped within template files.
        echo ob_get_clean();
    }

    public function addFragments($fragments)
    {
        ob_start();
        $items = WC()->cart ? WC()->cart->get_cart() : [];
        Helper::template('side-cart.php');
        $fragments['#himuon-side-cart'] = ob_get_clean();

        if ($this->shouldShowMiniCart()) {
            $fragments['#himuon-mini-cart .himuon-cart--mini-count'] = $this->renderMiniCartCount();
        }

        return $fragments;
    }

    private function renderMiniCartCount()
    {
        $cartCount = 0;
        if (function_exists('WC') && WC()->cart) {
            $cartCount = WC()->cart->get_cart_contents_count();
        }

        ob_start();
        ?>
        <span class="himuon-cart--mini-count"
              data-count="<?php echo esc_attr((string) absint($cartCount)); ?>">
            <?php echo esc_html((string) absint($cartCount)); ?>
        </span>
        <?php
        return (string) ob_get_clean();
    }

    public function updateCartItem()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => 'Cart not available.'], 400);
        }

        $cartItemKey = $this->getRequestString('cartItemKey');
        $quantity = $this->getRequestInt('quantity', 0);

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

        $cartItemKey = $this->getRequestString('cart_item_key');

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
            $raw = $this->getRequestNullableString($postKey);
            if (null === $raw) {
                $raw = $this->getRequestNullableString($key);
                $postKey = $key; // keep original key if you want
            }

            if ($raw === null) {
                continue;
            }

            $value = $raw;
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

        $postedScheme = $this->getRequestNullableString('convert_to_sub');
        if (null !== $postedScheme) {
            $subscriptionSelectionPosted = true;
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

    public function updateCartItemSubscription()
    {
        check_ajax_referer('himuon_flex_cart', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => 'Cart not available.'], 400);
        }

        $cartItemKey = $this->getRequestString('cart_item_key');
        if ('' === $cartItemKey) {
            wp_send_json_error(['message' => 'Invalid cart data.'], 400);
        }

        $cart = WC()->cart->get_cart();
        $cartItem = isset($cart[$cartItemKey]) ? $cart[$cartItemKey] : null;
        if (!$cartItem || empty($cartItem['data'])) {
            wp_send_json_error(['message' => __('Invalid Cart Item', 'himuon-flex-cart')], 400);
        }

        $product = $cartItem['data'];
        if (!class_exists('WCS_ATT_Product_Schemes') || !WCS_ATT_Product_Schemes::has_subscription_schemes($product)) {
            wp_send_json_error(['message' => __('Product is not subscription editable.', 'himuon-flex-cart')], 400);
        }

        $postedScheme = $this->getRequestNullableString('convert_to_sub');
        if (null === $postedScheme) {
            wp_send_json_error(['message' => __('Missing subscription option.', 'himuon-flex-cart')], 400);
        }

        $newScheme = WCS_ATT_Product_Schemes::parse_subscription_scheme_key($postedScheme);
        $availableSchemes = WCS_ATT_Product_Schemes::get_subscription_schemes($product);
        $hasForcedSubscription = WCS_ATT_Product_Schemes::has_forced_subscription_scheme($product);

        if (false === $newScheme && $hasForcedSubscription) {
            wp_send_json_error(['message' => __('This item is available only as a subscription.', 'himuon-flex-cart')], 400);
        }

        if (false !== $newScheme && !isset($availableSchemes[$newScheme])) {
            wp_send_json_error(['message' => __('Invalid subscription option.', 'himuon-flex-cart')], 400);
        }

        $existingScheme = isset($cartItem['wcsatt_data']['active_subscription_scheme'])
            ? $cartItem['wcsatt_data']['active_subscription_scheme']
            : null;

        if ($existingScheme === $newScheme) {
            wp_send_json_success([
                'no_change' => true,
            ]);
        }

        if (!isset(WC()->cart->cart_contents[$cartItemKey]['wcsatt_data']) || !is_array(WC()->cart->cart_contents[$cartItemKey]['wcsatt_data'])) {
            WC()->cart->cart_contents[$cartItemKey]['wcsatt_data'] = [];
        }
        WC()->cart->cart_contents[$cartItemKey]['wcsatt_data']['active_subscription_scheme'] = $newScheme;

        if (class_exists('WCS_ATT_Cart')) {
            WCS_ATT_Cart::apply_subscription_schemes(WC()->cart);
        }

        WC()->cart->calculate_totals();

        $fragments = apply_filters('woocommerce_add_to_cart_fragments', []);
        $cartHash = WC()->cart->get_cart_hash();

        wp_send_json_success([
            'cart_item_key' => $cartItemKey,
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

        $cartItemKey = $this->getRequestString('cart_item_key');

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

    private function renderSimpleSubscriptionForm($cartItem, $cartItemKey)
    {
        $viewData = EditSubscriptionItemView::build($cartItem, $cartItemKey);
        if (!$viewData) {
            return false;
        }

        ob_start();
        Helper::template('edit-subscription-item.php', $viewData);
        return ob_get_clean();
    }

}
