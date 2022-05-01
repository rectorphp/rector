<?php



/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme as p;
if (!\defined('GRAPHEME_EXTR_COUNT')) {
    \define('GRAPHEME_EXTR_COUNT', 0);
}
if (!\defined('GRAPHEME_EXTR_MAXBYTES')) {
    \define('GRAPHEME_EXTR_MAXBYTES', 1);
}
if (!\defined('GRAPHEME_EXTR_MAXCHARS')) {
    \define('GRAPHEME_EXTR_MAXCHARS', 2);
}
if (!\function_exists('grapheme_extract')) {
    /**
     * @return string|true
     */
    function grapheme_extract(?string $haystack, ?int $size, ?int $type = \GRAPHEME_EXTR_COUNT, ?int $offset = 0, &$next = null)
    {
        return \RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme\Grapheme::grapheme_extract((string) $haystack, (int) $size, (int) $type, (int) $offset, $next);
    }
}
if (!\function_exists('grapheme_stripos')) {
    /**
     * @return int|true
     */
    function grapheme_stripos(?string $haystack, ?string $needle, ?int $offset = 0)
    {
        return \RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme\Grapheme::grapheme_stripos((string) $haystack, (string) $needle, (int) $offset);
    }
}
if (!\function_exists('grapheme_stristr')) {
    /**
     * @return string|true
     */
    function grapheme_stristr(?string $haystack, ?string $needle, ?bool $beforeNeedle = \false)
    {
        return \RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme\Grapheme::grapheme_stristr((string) $haystack, (string) $needle, (bool) $beforeNeedle);
    }
}
if (!\function_exists('grapheme_strlen')) {
    /**
     * @return int|true|null
     */
    function grapheme_strlen(?string $string)
    {
        return \RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme\Grapheme::grapheme_strlen((string) $string);
    }
}
if (!\function_exists('grapheme_strpos')) {
    /**
     * @return int|true
     */
    function grapheme_strpos(?string $haystack, ?string $needle, ?int $offset = 0)
    {
        return \RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme\Grapheme::grapheme_strpos((string) $haystack, (string) $needle, (int) $offset);
    }
}
if (!\function_exists('grapheme_strripos')) {
    /**
     * @return int|true
     */
    function grapheme_strripos(?string $haystack, ?string $needle, ?int $offset = 0)
    {
        return \RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme\Grapheme::grapheme_strripos((string) $haystack, (string) $needle, (int) $offset);
    }
}
if (!\function_exists('grapheme_strrpos')) {
    /**
     * @return int|true
     */
    function grapheme_strrpos(?string $haystack, ?string $needle, ?int $offset = 0)
    {
        return \RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme\Grapheme::grapheme_strrpos((string) $haystack, (string) $needle, (int) $offset);
    }
}
if (!\function_exists('grapheme_strstr')) {
    /**
     * @return string|true
     */
    function grapheme_strstr(?string $haystack, ?string $needle, ?bool $beforeNeedle = \false)
    {
        return \RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme\Grapheme::grapheme_strstr((string) $haystack, (string) $needle, (bool) $beforeNeedle);
    }
}
if (!\function_exists('grapheme_substr')) {
    /**
     * @return string|true
     */
    function grapheme_substr(?string $string, ?int $offset, ?int $length = null)
    {
        return \RectorPrefix20220501\Symfony\Polyfill\Intl\Grapheme\Grapheme::grapheme_substr((string) $string, (int) $offset, $length);
    }
}
