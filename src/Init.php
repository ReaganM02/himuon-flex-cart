<?php

namespace Himuon\Flex\Cart;

use Himuon\Flex\Cart\Frontend\SideCart;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Init
{
    public static function load()
    {
        $sideCart = new SideCart();
        $sideCart->register();

    }
}

