<?php



/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use RectorPrefix20211231\Symfony\Polyfill\Ctype as p;
if (!\function_exists('ctype_alnum')) {
    /**
     * @param mixed $text
     */
    function ctype_alnum($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_alnum($text);
    }
}
if (!\function_exists('ctype_alpha')) {
    /**
     * @param mixed $text
     */
    function ctype_alpha($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_alpha($text);
    }
}
if (!\function_exists('ctype_cntrl')) {
    /**
     * @param mixed $text
     */
    function ctype_cntrl($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_cntrl($text);
    }
}
if (!\function_exists('ctype_digit')) {
    /**
     * @param mixed $text
     */
    function ctype_digit($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_digit($text);
    }
}
if (!\function_exists('ctype_graph')) {
    /**
     * @param mixed $text
     */
    function ctype_graph($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_graph($text);
    }
}
if (!\function_exists('ctype_lower')) {
    /**
     * @param mixed $text
     */
    function ctype_lower($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_lower($text);
    }
}
if (!\function_exists('ctype_print')) {
    /**
     * @param mixed $text
     */
    function ctype_print($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_print($text);
    }
}
if (!\function_exists('ctype_punct')) {
    /**
     * @param mixed $text
     */
    function ctype_punct($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_punct($text);
    }
}
if (!\function_exists('ctype_space')) {
    /**
     * @param mixed $text
     */
    function ctype_space($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_space($text);
    }
}
if (!\function_exists('ctype_upper')) {
    /**
     * @param mixed $text
     */
    function ctype_upper($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_upper($text);
    }
}
if (!\function_exists('ctype_xdigit')) {
    /**
     * @param mixed $text
     */
    function ctype_xdigit($text) : bool
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Ctype\Ctype::ctype_xdigit($text);
    }
}
