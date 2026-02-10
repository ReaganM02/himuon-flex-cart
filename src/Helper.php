<?php

namespace Himuon\Flex\Cart;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Helper
{
    public static function template(string $templatePath, $data = [])
    {
        require HIMUON_FLEX_CART_PATH . 'templates/' . $templatePath;
    }

    public static function allowSVG()
    {
        $allowedSVG = [
            'svg' => [
                'xmlns' => true,
                'viewbox' => true,
                'viewBox' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'width' => true,
                'height' => true,
                'class' => true,
                'aria-hidden' => true,
                'role' => true,
            ],
            'path' => [
                'd' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
            ],
        ];
        return $allowedSVG;
    }
}