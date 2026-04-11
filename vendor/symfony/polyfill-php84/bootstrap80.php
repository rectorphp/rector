<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Polyfill\Php84 as p;

if (extension_loaded('mbstring')) {
    if (!function_exists('mb_ucfirst')) {
        function mb_ucfirst(string $string, ?string $encoding = null): string { return p\Php84::mb_ucfirst($string, $encoding); }
    }

    if (!function_exists('mb_lcfirst')) {
        function mb_lcfirst(string $string, ?string $encoding = null): string { return p\Php84::mb_lcfirst($string, $encoding); }
    }

    if (!function_exists('mb_trim')) {
        function mb_trim(string $string, ?string $characters = null, ?string $encoding = null): string { return p\Php84::mb_trim($string, $characters, $encoding); }
    }

    if (!function_exists('mb_ltrim')) {
        function mb_ltrim(string $string, ?string $characters = null, ?string $encoding = null): string { return p\Php84::mb_ltrim($string, $characters, $encoding); }
    }

    if (!function_exists('mb_rtrim')) {
        function mb_rtrim(string $string, ?string $characters = null, ?string $encoding = null): string { return p\Php84::mb_rtrim($string, $characters, $encoding); }
    }
}

if (extension_loaded('bcmath')) {
    if (!function_exists('bcceil')) {
        function bcceil(string $num): string { return p\Php84::bcceil($num); }
    }
    if (!function_exists('bcdivmod')) {
        function bcdivmod(string $num1, string $num2, ?int $scale = null): ?array { return p\Php84::bcdivmod($num1, $num2, $scale); }
    }
    if (!function_exists('bcfloor')) {
        function bcfloor(string $num): string { return p\Php84::bcfloor($num); }
    }
    if (!function_exists('bcround')) {
        function bcround(string $num, int $precision = 0, $mode = RoundingMode::HalfAwayFromZero): string { return p\Php84::bcround($num, $precision, $mode); }
    }
}

if (\PHP_VERSION_ID >= 80200) {
    return require __DIR__.'/bootstrap82.php';
}

if (extension_loaded('intl') && !function_exists('grapheme_str_split')) {
    function grapheme_str_split(string $string, int $length = 1) { return p\Php84::grapheme_str_split($string, $length); }
}
