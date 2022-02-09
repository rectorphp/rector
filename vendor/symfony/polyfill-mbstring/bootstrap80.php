<?php



/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use RectorPrefix20220209\Symfony\Polyfill\Mbstring as p;
if (!\function_exists('mb_convert_encoding')) {
    /**
     * @param mixed[]|string|null $string
     * @param mixed[]|string|null $from_encoding
     * @return mixed[]|string|true
     */
    function mb_convert_encoding($string, ?string $to_encoding, $from_encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_convert_encoding($string ?? '', (string) $to_encoding, $from_encoding);
    }
}
if (!\function_exists('mb_decode_mimeheader')) {
    function mb_decode_mimeheader(?string $string) : string
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_decode_mimeheader((string) $string);
    }
}
if (!\function_exists('mb_encode_mimeheader')) {
    function mb_encode_mimeheader(?string $string, ?string $charset = null, ?string $transfer_encoding = null, ?string $newline = "\r\n", ?int $indent = 0) : string
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_encode_mimeheader((string) $string, $charset, $transfer_encoding, (string) $newline, (int) $indent);
    }
}
if (!\function_exists('mb_decode_numericentity')) {
    function mb_decode_numericentity(?string $string, array $map, ?string $encoding = null) : string
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_decode_numericentity((string) $string, $map, $encoding);
    }
}
if (!\function_exists('mb_encode_numericentity')) {
    function mb_encode_numericentity(?string $string, array $map, ?string $encoding = null, ?bool $hex = \false) : string
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_encode_numericentity((string) $string, $map, $encoding, (bool) $hex);
    }
}
if (!\function_exists('mb_convert_case')) {
    function mb_convert_case(?string $string, ?int $mode, ?string $encoding = null) : string
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_convert_case((string) $string, (int) $mode, $encoding);
    }
}
if (!\function_exists('mb_internal_encoding')) {
    /**
     * @return bool|string
     */
    function mb_internal_encoding(?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_internal_encoding($encoding);
    }
}
if (!\function_exists('mb_language')) {
    /**
     * @return bool|string
     */
    function mb_language(?string $language = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_language($language);
    }
}
if (!\function_exists('mb_list_encodings')) {
    function mb_list_encodings() : array
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_list_encodings();
    }
}
if (!\function_exists('mb_encoding_aliases')) {
    function mb_encoding_aliases(?string $encoding) : array
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_encoding_aliases((string) $encoding);
    }
}
if (!\function_exists('mb_check_encoding')) {
    /**
     * @param mixed[]|string|null $value
     */
    function mb_check_encoding($value = null, ?string $encoding = null) : bool
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_check_encoding($value, $encoding);
    }
}
if (!\function_exists('mb_detect_encoding')) {
    /**
     * @param mixed[]|string|null $encodings
     * @return string|true
     */
    function mb_detect_encoding(?string $string, $encodings = null, ?bool $strict = \false)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_detect_encoding((string) $string, $encodings, (bool) $strict);
    }
}
if (!\function_exists('mb_detect_order')) {
    /**
     * @param mixed[]|string|null $encoding
     * @return mixed[]|bool
     */
    function mb_detect_order($encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_detect_order($encoding);
    }
}
if (!\function_exists('mb_parse_str')) {
    function mb_parse_str(?string $string, &$result = []) : bool
    {
        \parse_str((string) $string, $result);
        return (bool) $result;
    }
}
if (!\function_exists('mb_strlen')) {
    function mb_strlen(?string $string, ?string $encoding = null) : int
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strlen((string) $string, $encoding);
    }
}
if (!\function_exists('mb_strpos')) {
    /**
     * @return int|true
     */
    function mb_strpos(?string $haystack, ?string $needle, ?int $offset = 0, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strpos((string) $haystack, (string) $needle, (int) $offset, $encoding);
    }
}
if (!\function_exists('mb_strtolower')) {
    function mb_strtolower(?string $string, ?string $encoding = null) : string
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strtolower((string) $string, $encoding);
    }
}
if (!\function_exists('mb_strtoupper')) {
    function mb_strtoupper(?string $string, ?string $encoding = null) : string
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strtoupper((string) $string, $encoding);
    }
}
if (!\function_exists('mb_substitute_character')) {
    /**
     * @param int|string|null $substitute_character
     * @return bool|int|string
     */
    function mb_substitute_character($substitute_character = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_substitute_character($substitute_character);
    }
}
if (!\function_exists('mb_substr')) {
    function mb_substr(?string $string, ?int $start, ?int $length = null, ?string $encoding = null) : string
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_substr((string) $string, (int) $start, $length, $encoding);
    }
}
if (!\function_exists('mb_stripos')) {
    /**
     * @return int|true
     */
    function mb_stripos(?string $haystack, ?string $needle, ?int $offset = 0, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_stripos((string) $haystack, (string) $needle, (int) $offset, $encoding);
    }
}
if (!\function_exists('mb_stristr')) {
    /**
     * @return string|true
     */
    function mb_stristr(?string $haystack, ?string $needle, ?bool $before_needle = \false, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_stristr((string) $haystack, (string) $needle, (bool) $before_needle, $encoding);
    }
}
if (!\function_exists('mb_strrchr')) {
    /**
     * @return string|true
     */
    function mb_strrchr(?string $haystack, ?string $needle, ?bool $before_needle = \false, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strrchr((string) $haystack, (string) $needle, (bool) $before_needle, $encoding);
    }
}
if (!\function_exists('mb_strrichr')) {
    /**
     * @return string|true
     */
    function mb_strrichr(?string $haystack, ?string $needle, ?bool $before_needle = \false, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strrichr((string) $haystack, (string) $needle, (bool) $before_needle, $encoding);
    }
}
if (!\function_exists('mb_strripos')) {
    /**
     * @return int|true
     */
    function mb_strripos(?string $haystack, ?string $needle, ?int $offset = 0, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strripos((string) $haystack, (string) $needle, (int) $offset, $encoding);
    }
}
if (!\function_exists('mb_strrpos')) {
    /**
     * @return int|true
     */
    function mb_strrpos(?string $haystack, ?string $needle, ?int $offset = 0, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strrpos((string) $haystack, (string) $needle, (int) $offset, $encoding);
    }
}
if (!\function_exists('mb_strstr')) {
    /**
     * @return string|true
     */
    function mb_strstr(?string $haystack, ?string $needle, ?bool $before_needle = \false, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strstr((string) $haystack, (string) $needle, (bool) $before_needle, $encoding);
    }
}
if (!\function_exists('mb_get_info')) {
    /**
     * @return mixed[]|int|string|true
     */
    function mb_get_info(?string $type = 'all')
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_get_info((string) $type);
    }
}
if (!\function_exists('mb_http_output')) {
    /**
     * @return bool|string
     */
    function mb_http_output(?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_http_output($encoding);
    }
}
if (!\function_exists('mb_strwidth')) {
    function mb_strwidth(?string $string, ?string $encoding = null) : int
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_strwidth((string) $string, $encoding);
    }
}
if (!\function_exists('mb_substr_count')) {
    function mb_substr_count(?string $haystack, ?string $needle, ?string $encoding = null) : int
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_substr_count((string) $haystack, (string) $needle, $encoding);
    }
}
if (!\function_exists('mb_output_handler')) {
    function mb_output_handler(?string $string, ?int $status) : string
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_output_handler((string) $string, (int) $status);
    }
}
if (!\function_exists('mb_http_input')) {
    /**
     * @return mixed[]|string|true
     */
    function mb_http_input(?string $type = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_http_input($type);
    }
}
if (!\function_exists('mb_convert_variables')) {
    /**
     * @param mixed[]|string|null $from_encoding
     * @return string|true
     * @param mixed $var
     * @param mixed ...$vars
     */
    function mb_convert_variables(?string $to_encoding, $from_encoding, &$var, &...$vars)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_convert_variables((string) $to_encoding, $from_encoding ?? '', $var, ...$vars);
    }
}
if (!\function_exists('mb_ord')) {
    /**
     * @return int|true
     */
    function mb_ord(?string $string, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_ord((string) $string, $encoding);
    }
}
if (!\function_exists('mb_chr')) {
    /**
     * @return string|true
     */
    function mb_chr(?int $codepoint, ?string $encoding = null)
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_chr((int) $codepoint, $encoding);
    }
}
if (!\function_exists('mb_scrub')) {
    function mb_scrub(?string $string, ?string $encoding = null) : string
    {
        $encoding = $encoding ?? \mb_internal_encoding();
        return \mb_convert_encoding((string) $string, $encoding, $encoding);
    }
}
if (!\function_exists('mb_str_split')) {
    function mb_str_split(?string $string, ?int $length = 1, ?string $encoding = null) : array
    {
        return \RectorPrefix20220209\Symfony\Polyfill\Mbstring\Mbstring::mb_str_split((string) $string, (int) $length, $encoding);
    }
}
if (\extension_loaded('mbstring')) {
    return;
}
if (!\defined('MB_CASE_UPPER')) {
    \define('MB_CASE_UPPER', 0);
}
if (!\defined('MB_CASE_LOWER')) {
    \define('MB_CASE_LOWER', 1);
}
if (!\defined('MB_CASE_TITLE')) {
    \define('MB_CASE_TITLE', 2);
}
