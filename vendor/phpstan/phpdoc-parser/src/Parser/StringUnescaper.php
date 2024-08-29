<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use PHPStan\ShouldNotHappenException;
use function chr;
use function hexdec;
use function octdec;
use function preg_replace_callback;
use function str_replace;
use function substr;
class StringUnescaper
{
    private const REPLACEMENTS = ['\\' => '\\', 'n' => "\n", 'r' => "\r", 't' => "\t", 'f' => "\f", 'v' => "\v", 'e' => "\x1b"];
    public static function unescapeString(string $string) : string
    {
        $quote = $string[0];
        if ($quote === '\'') {
            return str_replace(['\\\\', '\\\''], ['\\', '\''], substr($string, 1, -1));
        }
        return self::parseEscapeSequences(substr($string, 1, -1), '"');
    }
    /**
     * Implementation based on https://github.com/nikic/PHP-Parser/blob/b0edd4c41111042d43bb45c6c657b2e0db367d9e/lib/PhpParser/Node/Scalar/String_.php#L90-L130
     */
    private static function parseEscapeSequences(string $str, string $quote) : string
    {
        $str = str_replace('\\' . $quote, $quote, $str);
        return preg_replace_callback('~\\\\([\\\\nrtfve]|[xX][0-9a-fA-F]{1,2}|[0-7]{1,3}|u\\{([0-9a-fA-F]+)\\})~', static function ($matches) {
            $str = $matches[1];
            if (isset(self::REPLACEMENTS[$str])) {
                return self::REPLACEMENTS[$str];
            }
            if ($str[0] === 'x' || $str[0] === 'X') {
                return chr((int) hexdec(substr($str, 1)));
            }
            if ($str[0] === 'u') {
                if (!isset($matches[2])) {
                    throw new ShouldNotHappenException();
                }
                return self::codePointToUtf8((int) hexdec($matches[2]));
            }
            return chr((int) octdec($str));
        }, $str);
    }
    /**
     * Implementation based on https://github.com/nikic/PHP-Parser/blob/b0edd4c41111042d43bb45c6c657b2e0db367d9e/lib/PhpParser/Node/Scalar/String_.php#L132-L154
     */
    private static function codePointToUtf8(int $num) : string
    {
        if ($num <= 0x7f) {
            return chr($num);
        }
        if ($num <= 0x7ff) {
            return chr(($num >> 6) + 0xc0) . chr(($num & 0x3f) + 0x80);
        }
        if ($num <= 0xffff) {
            return chr(($num >> 12) + 0xe0) . chr(($num >> 6 & 0x3f) + 0x80) . chr(($num & 0x3f) + 0x80);
        }
        if ($num <= 0x1fffff) {
            return chr(($num >> 18) + 0xf0) . chr(($num >> 12 & 0x3f) + 0x80) . chr(($num >> 6 & 0x3f) + 0x80) . chr(($num & 0x3f) + 0x80);
        }
        // Invalid UTF-8 codepoint escape sequence: Codepoint too large
        return "�";
    }
}
