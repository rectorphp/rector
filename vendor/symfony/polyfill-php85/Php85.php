<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Symfony\Polyfill\Php85;

/**
 * @author Pierre Ambroise <pierre27.ambroise@gmail.com>
 *
 * @internal
 */
final class Php85
{
    public static function get_error_handler(): ?callable
    {
        $handler = set_error_handler(null);
        restore_error_handler();
        return $handler;
    }
    public static function get_exception_handler(): ?callable
    {
        $handler = set_exception_handler(null);
        restore_exception_handler();
        return $handler;
    }
    public static function array_first(array $array)
    {
        foreach ($array as $value) {
            return $value;
        }
        return null;
    }
    public static function array_last(array $array)
    {
        return $array ? current(array_slice($array, -1)) : null;
    }
}
