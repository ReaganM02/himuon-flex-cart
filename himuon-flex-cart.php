<?php

use Himuon\Flex\Cart\Init;
/**
 * Plugin Name:       Himuon Flex Cart
 * Description:       AJAX-powered WooCommerce side cart focused on UX, with in-cart quantity/variation/subscription editing, coupon apply/remove flow, free-shipping progress, and developer-friendly hooks.
 * Version:           1.0.0
 * Author:            Reagan Mahinay
 * Author URI:        https://github.com/ReaganM02
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       himuon-flex-cart
 * Requires at least: 6.9.1
 * Requires PHP: 8.0
 * Tested up to: 6.9.1
 * Requires Plugins: woocommerce
 * @package himuon-flex-cart
 */

define('HIMUON_FLEX_CART_VERSION', '1.0.0');
define('HIMUON_FLEX_CART_PATH', plugin_dir_path(__FILE__));
define('HIMUON_FLEX_CART_URL', plugin_dir_url(__FILE__));

require_once HIMUON_FLEX_CART_PATH . 'vendor/autoload.php';


add_action('plugins_loaded', [Init::class, 'load']);