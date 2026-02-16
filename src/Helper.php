<?php

namespace Himuon\Flex\Cart;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Helper
{
    /** @var array<string, bool> */
    private static array $templateStack = [];

    public static function template(string $templatePath, $data = [])
    {
        $relativePath = self::normalizeTemplatePath($templatePath);
        if ('' === $relativePath) {
            return;
        }

        $resolvedPath = self::locateTemplate($relativePath);
        if ('' === $resolvedPath) {
            return;
        }

        $stackKey = $resolvedPath;
        if (isset(self::$templateStack[$stackKey])) {
            return;
        }

        self::$templateStack[$stackKey] = true;
        try {
            require $resolvedPath;
        } finally {
            unset(self::$templateStack[$stackKey]);
        }
    }

    private static function normalizeTemplatePath(string $templatePath): string
    {
        $relativePath = str_replace('\\', '/', ltrim($templatePath, '/'));
        if ('' === $relativePath || false !== strpos($relativePath, '..')) {
            return '';
        }

        return $relativePath;
    }

    private static function locateTemplate(string $relativePath): string
    {
        $defaultTemplate = HIMUON_FLEX_CART_PATH . 'templates/' . $relativePath;
        $templateSubdir = (string) apply_filters(
            'himuon_flex_cart_template_subdir',
            'himuon-flex-cart'
        );
        $templateSubdir = trim(str_replace('\\', '/', $templateSubdir), '/');

        $locatedTemplate = '';
        if (function_exists('locate_template') && '' !== $templateSubdir) {
            $candidate = $templateSubdir . '/' . $relativePath;
            $found = locate_template($candidate, false, false);
            if (is_string($found) && '' !== $found) {
                $locatedTemplate = $found;
            }
        }

        if ('' === $locatedTemplate && file_exists($defaultTemplate)) {
            $locatedTemplate = $defaultTemplate;
        }

        $filtered = (string) apply_filters(
            'himuon_flex_cart_located_template',
            $locatedTemplate,
            $relativePath,
            $defaultTemplate
        );

        if ('' !== $filtered && file_exists($filtered)) {
            return $filtered;
        }

        return '';
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
