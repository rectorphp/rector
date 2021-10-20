<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Utils;

use RectorPrefix20211020\Nette;
use function is_array, is_object, strlen;
/**
 * String tools library.
 */
class Strings
{
    use Nette\StaticClass;
    public const TRIM_CHARACTERS = " \t\n\r\0\v¬†";
    /**
     * Checks if the string is valid in UTF-8 encoding.
     * @param string $s
     */
    public static function checkEncoding($s) : bool
    {
        return $s === self::fixEncoding($s);
    }
    /**
     * Removes all invalid UTF-8 characters from a string.
     * @param string $s
     */
    public static function fixEncoding($s) : string
    {
        // removes xD800-xDFFF, x110000 and higher
        return \htmlspecialchars_decode(\htmlspecialchars($s, \ENT_NOQUOTES | \ENT_IGNORE, 'UTF-8'), \ENT_NOQUOTES);
    }
    /**
     * Returns a specific character in UTF-8 from code point (number in range 0x0000..D7FF or 0xE000..10FFFF).
     * @throws Nette\InvalidArgumentException if code point is not in valid range
     * @param int $code
     */
    public static function chr($code) : string
    {
        if ($code < 0 || $code >= 0xd800 && $code <= 0xdfff || $code > 0x10ffff) {
            throw new \RectorPrefix20211020\Nette\InvalidArgumentException('Code point must be in range 0x0 to 0xD7FF or 0xE000 to 0x10FFFF.');
        } elseif (!\extension_loaded('iconv')) {
            throw new \RectorPrefix20211020\Nette\NotSupportedException(__METHOD__ . '() requires ICONV extension that is not loaded.');
        }
        return \iconv('UTF-32BE', 'UTF-8//IGNORE', \pack('N', $code));
    }
    /**
     * Starts the $haystack string with the prefix $needle?
     * @param string $haystack
     * @param string $needle
     */
    public static function startsWith($haystack, $needle) : bool
    {
        return \strncmp($haystack, $needle, \strlen($needle)) === 0;
    }
    /**
     * Ends the $haystack string with the suffix $needle?
     * @param string $haystack
     * @param string $needle
     */
    public static function endsWith($haystack, $needle) : bool
    {
        return $needle === '' || \substr($haystack, -\strlen($needle)) === $needle;
    }
    /**
     * Does $haystack contain $needle?
     * @param string $haystack
     * @param string $needle
     */
    public static function contains($haystack, $needle) : bool
    {
        return \strpos($haystack, $needle) !== \false;
    }
    /**
     * Returns a part of UTF-8 string specified by starting position and length. If start is negative,
     * the returned string will start at the start'th character from the end of string.
     * @param string $s
     * @param int $start
     * @param int|null $length
     */
    public static function substring($s, $start, $length = null) : string
    {
        if (\function_exists('mb_substr')) {
            return \mb_substr($s, $start, $length, 'UTF-8');
            // MB is much faster
        } elseif (!\extension_loaded('iconv')) {
            throw new \RectorPrefix20211020\Nette\NotSupportedException(__METHOD__ . '() requires extension ICONV or MBSTRING, neither is loaded.');
        } elseif ($length === null) {
            $length = self::length($s);
        } elseif ($start < 0 && $length < 0) {
            $start += self::length($s);
            // unifies iconv_substr behavior with mb_substr
        }
        return \iconv_substr($s, $start, $length, 'UTF-8');
    }
    /**
     * Removes control characters, normalizes line breaks to `\n`, removes leading and trailing blank lines,
     * trims end spaces on lines, normalizes UTF-8 to the normal form of NFC.
     * @param string $s
     */
    public static function normalize($s) : string
    {
        // convert to compressed normal form (NFC)
        if (\class_exists('Normalizer', \false) && ($n = \Normalizer::normalize($s, \Normalizer::FORM_C)) !== \false) {
            $s = $n;
        }
        $s = self::normalizeNewLines($s);
        // remove control characters; leave \t + \n
        $s = self::pcre('preg_replace', ['#[\\x00-\\x08\\x0B-\\x1F\\x7F-\\x9F]+#u', '', $s]);
        // right trim
        $s = self::pcre('preg_replace', ['#[\\t ]+$#m', '', $s]);
        // leading and trailing blank lines
        $s = \trim($s, "\n");
        return $s;
    }
    /**
     * Standardize line endings to unix-like.
     * @param string $s
     */
    public static function normalizeNewLines($s) : string
    {
        return \str_replace(["\r\n", "\r"], "\n", $s);
    }
    /**
     * Converts UTF-8 string to ASCII, ie removes diacritics etc.
     * @param string $s
     */
    public static function toAscii($s) : string
    {
        $iconv = \defined('ICONV_IMPL') ? \trim(\ICONV_IMPL, '"\'') : null;
        static $transliterator = null;
        if ($transliterator === null) {
            if (\class_exists('Transliterator', \false)) {
                $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
            } else {
                \trigger_error(__METHOD__ . "(): it is recommended to enable PHP extensions 'intl'.", \E_USER_NOTICE);
                $transliterator = \false;
            }
        }
        // remove control characters and check UTF-8 validity
        $s = self::pcre('preg_replace', ['#[^\\x09\\x0A\\x0D\\x20-\\x7E\\xA0-\\x{2FF}\\x{370}-\\x{10FFFF}]#u', '', $s]);
        // transliteration (by Transliterator and iconv) is not optimal, replace some characters directly
        $s = \strtr($s, ["‚Äû" => '"', "‚Äú" => '"', "‚Äù" => '"', "‚Äö" => "'", "‚Äò" => "'", "‚Äô" => "'", "¬∞" => '^', "–Ø" => 'Ya', "—è" => 'ya', "–Æ" => 'Yu', "—é" => 'yu', "√Ñ" => 'Ae', "√ñ" => 'Oe', "√ú" => 'Ue', "·∫û" => 'Ss', "√§" => 'ae', "√∂" => 'oe', "√º" => 'ue', "√ü" => 'ss']);
        // ‚Äû ‚Äú ‚Äù ‚Äö ‚Äò ‚Äô ¬∞ –Ø —è –Æ —é √Ñ √ñ √ú ·∫û √§ √∂ √º √ü
        if ($iconv !== 'libiconv') {
            $s = \strtr($s, ["¬Æ" => '(R)', "¬©" => '(c)', "‚Ä¶" => '...', "¬´" => '<<', "¬ª" => '>>', "¬£" => 'lb', "¬•" => 'yen', "¬≤" => '^2', "¬≥" => '^3', "¬µ" => 'u', "¬π" => '^1', "¬∫" => 'o', "¬ø" => '?', "Àä" => "'", "Àç" => '_', "Àù" => '"', "·øØ" => '', "‚Ç¨" => 'EUR', "‚Ñ¢" => 'TM', "‚ÑÆ" => 'e', "‚Üê" => '<-', "‚Üë" => '^', "‚Üí" => '->', "‚Üì" => 'V', "‚Üî" => '<->']);
            // ¬Æ ¬© ‚Ä¶ ¬´ ¬ª ¬£ ¬• ¬≤ ¬≥ ¬µ ¬π ¬∫ ¬ø Àä Àç Àù ·øØ ‚Ç¨ ‚Ñ¢ ‚ÑÆ ‚Üê ‚Üë ‚Üí ‚Üì ‚Üî
        }
        if ($transliterator) {
            $s = $transliterator->transliterate($s);
            // use iconv because The transliterator leaves some characters out of ASCII, eg ‚Üí  æ
            if ($iconv === 'glibc') {
                $s = \strtr($s, '?', "\1");
                // temporarily hide ? to distinguish them from the garbage that iconv creates
                $s = \iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
                $s = \str_replace(['?', "\1"], ['', '?'], $s);
                // remove garbage and restore ? characters
            } elseif ($iconv === 'libiconv') {
                $s = \iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            } else {
                // null or 'unknown' (#216)
                $s = self::pcre('preg_replace', ['#[^\\x00-\\x7F]++#', '', $s]);
                // remove non-ascii chars
            }
        } elseif ($iconv === 'glibc' || $iconv === 'libiconv') {
            // temporarily hide these characters to distinguish them from the garbage that iconv creates
            $s = \strtr($s, '`\'"^~?', "\1\2\3\4\5\6");
            if ($iconv === 'glibc') {
                // glibc implementation is very limited. transliterate into Windows-1250 and then into ASCII, so most Eastern European characters are preserved
                $s = \iconv('UTF-8', 'WINDOWS-1250//TRANSLIT//IGNORE', $s);
                $s = \strtr($s, "•£ºåßä™çèéØπ≥æúö∫ùüûø¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—“”‘’÷◊ÿŸ⁄€‹›ﬁﬂ‡·‚„‰ÂÊÁËÈÍÎÏÌÓÔÒÚÛÙıˆ¯˘˙˚¸˝˛ñ†ãóõ¶≠∑", 'ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt- <->|-.');
                $s = self::pcre('preg_replace', ['#[^\\x00-\\x7F]++#', '', $s]);
            } else {
                $s = \iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            }
            // remove garbage that iconv creates during transliteration (eg √ù -> Y')
            $s = \str_replace(['`', "'", '"', '^', '~', '?'], '', $s);
            // restore temporarily hidden characters
            $s = \strtr($s, "\1\2\3\4\5\6", '`\'"^~?');
        } else {
            $s = self::pcre('preg_replace', ['#[^\\x00-\\x7F]++#', '', $s]);
            // remove non-ascii chars
        }
        return $s;
    }
    /**
     * Modifies the UTF-8 string to the form used in the URL, ie removes diacritics and replaces all characters
     * except letters of the English alphabet and numbers with a hyphens.
     * @param string $s
     * @param string|null $charlist
     * @param bool $lower
     */
    public static function webalize($s, $charlist = null, $lower = \true) : string
    {
        $s = self::toAscii($s);
        if ($lower) {
            $s = \strtolower($s);
        }
        $s = self::pcre('preg_replace', ['#[^a-z0-9' . ($charlist !== null ? \preg_quote($charlist, '#') : '') . ']+#i', '-', $s]);
        $s = \trim($s, '-');
        return $s;
    }
    /**
     * Truncates a UTF-8 string to given maximal length, while trying not to split whole words. Only if the string is truncated,
     * an ellipsis (or something else set with third argument) is appended to the string.
     * @param string $s
     * @param int $maxLen
     * @param string $append
     */
    public static function truncate($s, $maxLen, $append = "‚Ä¶") : string
    {
        if (self::length($s) > $maxLen) {
            $maxLen -= self::length($append);
            if ($maxLen < 1) {
                return $append;
            } elseif ($matches = self::match($s, '#^.{1,' . $maxLen . '}(?=[\\s\\x00-/:-@\\[-`{-~])#us')) {
                return $matches[0] . $append;
            } else {
                return self::substring($s, 0, $maxLen) . $append;
            }
        }
        return $s;
    }
    /**
     * Indents a multiline text from the left. Second argument sets how many indentation chars should be used,
     * while the indent itself is the third argument (*tab* by default).
     * @param string $s
     * @param int $level
     * @param string $chars
     */
    public static function indent($s, $level = 1, $chars = "\t") : string
    {
        if ($level > 0) {
            $s = self::replace($s, '#(?:^|[\\r\\n]+)(?=[^\\r\\n])#', '$0' . \str_repeat($chars, $level));
        }
        return $s;
    }
    /**
     * Converts all characters of UTF-8 string to lower case.
     * @param string $s
     */
    public static function lower($s) : string
    {
        return \mb_strtolower($s, 'UTF-8');
    }
    /**
     * Converts the first character of a UTF-8 string to lower case and leaves the other characters unchanged.
     * @param string $s
     */
    public static function firstLower($s) : string
    {
        return self::lower(self::substring($s, 0, 1)) . self::substring($s, 1);
    }
    /**
     * Converts all characters of a UTF-8 string to upper case.
     * @param string $s
     */
    public static function upper($s) : string
    {
        return \mb_strtoupper($s, 'UTF-8');
    }
    /**
     * Converts the first character of a UTF-8 string to upper case and leaves the other characters unchanged.
     * @param string $s
     */
    public static function firstUpper($s) : string
    {
        return self::upper(self::substring($s, 0, 1)) . self::substring($s, 1);
    }
    /**
     * Converts the first character of every word of a UTF-8 string to upper case and the others to lower case.
     * @param string $s
     */
    public static function capitalize($s) : string
    {
        return \mb_convert_case($s, \MB_CASE_TITLE, 'UTF-8');
    }
    /**
     * Compares two UTF-8 strings or their parts, without taking character case into account. If length is null, whole strings are compared,
     * if it is negative, the corresponding number of characters from the end of the strings is compared,
     * otherwise the appropriate number of characters from the beginning is compared.
     * @param string $left
     * @param string $right
     * @param int|null $length
     */
    public static function compare($left, $right, $length = null) : bool
    {
        if (\class_exists('Normalizer', \false)) {
            $left = \Normalizer::normalize($left, \Normalizer::FORM_D);
            // form NFD is faster
            $right = \Normalizer::normalize($right, \Normalizer::FORM_D);
            // form NFD is faster
        }
        if ($length < 0) {
            $left = self::substring($left, $length, -$length);
            $right = self::substring($right, $length, -$length);
        } elseif ($length !== null) {
            $left = self::substring($left, 0, $length);
            $right = self::substring($right, 0, $length);
        }
        return self::lower($left) === self::lower($right);
    }
    /**
     * Finds the common prefix of strings or returns empty string if the prefix was not found.
     * @param  string[]  $strings
     */
    public static function findPrefix($strings) : string
    {
        $first = \array_shift($strings);
        for ($i = 0; $i < \strlen($first); $i++) {
            foreach ($strings as $s) {
                if (!isset($s[$i]) || $first[$i] !== $s[$i]) {
                    while ($i && $first[$i - 1] >= "Ä" && $first[$i] >= "Ä" && $first[$i] < "¿") {
                        $i--;
                    }
                    return \substr($first, 0, $i);
                }
            }
        }
        return $first;
    }
    /**
     * Returns number of characters (not bytes) in UTF-8 string.
     * That is the number of Unicode code points which may differ from the number of graphemes.
     * @param string $s
     */
    public static function length($s) : int
    {
        return \function_exists('mb_strlen') ? \mb_strlen($s, 'UTF-8') : \strlen(\utf8_decode($s));
    }
    /**
     * Removes all left and right side spaces (or the characters passed as second argument) from a UTF-8 encoded string.
     * @param string $s
     * @param string $charlist
     */
    public static function trim($s, $charlist = self::TRIM_CHARACTERS) : string
    {
        $charlist = \preg_quote($charlist, '#');
        return self::replace($s, '#^[' . $charlist . ']+|[' . $charlist . ']+$#Du', '');
    }
    /**
     * Pads a UTF-8 string to given length by prepending the $pad string to the beginning.
     * @param string $s
     * @param int $length
     * @param string $pad
     */
    public static function padLeft($s, $length, $pad = ' ') : string
    {
        $length = \max(0, $length - self::length($s));
        $padLen = self::length($pad);
        return \str_repeat($pad, (int) ($length / $padLen)) . self::substring($pad, 0, $length % $padLen) . $s;
    }
    /**
     * Pads UTF-8 string to given length by appending the $pad string to the end.
     * @param string $s
     * @param int $length
     * @param string $pad
     */
    public static function padRight($s, $length, $pad = ' ') : string
    {
        $length = \max(0, $length - self::length($s));
        $padLen = self::length($pad);
        return $s . \str_repeat($pad, (int) ($length / $padLen)) . self::substring($pad, 0, $length % $padLen);
    }
    /**
     * Reverses UTF-8 string.
     * @param string $s
     */
    public static function reverse($s) : string
    {
        if (!\extension_loaded('iconv')) {
            throw new \RectorPrefix20211020\Nette\NotSupportedException(__METHOD__ . '() requires ICONV extension that is not loaded.');
        }
        return \iconv('UTF-32LE', 'UTF-8', \strrev(\iconv('UTF-8', 'UTF-32BE', $s)));
    }
    /**
     * Returns part of $haystack before $nth occurence of $needle or returns null if the needle was not found.
     * Negative value means searching from the end.
     * @param string $haystack
     * @param string $needle
     * @param int $nth
     */
    public static function before($haystack, $needle, $nth = 1) : ?string
    {
        $pos = self::pos($haystack, $needle, $nth);
        return $pos === null ? null : \substr($haystack, 0, $pos);
    }
    /**
     * Returns part of $haystack after $nth occurence of $needle or returns null if the needle was not found.
     * Negative value means searching from the end.
     * @param string $haystack
     * @param string $needle
     * @param int $nth
     */
    public static function after($haystack, $needle, $nth = 1) : ?string
    {
        $pos = self::pos($haystack, $needle, $nth);
        return $pos === null ? null : \substr($haystack, $pos + \strlen($needle));
    }
    /**
     * Returns position in characters of $nth occurence of $needle in $haystack or null if the $needle was not found.
     * Negative value of `$nth` means searching from the end.
     * @param string $haystack
     * @param string $needle
     * @param int $nth
     */
    public static function indexOf($haystack, $needle, $nth = 1) : ?int
    {
        $pos = self::pos($haystack, $needle, $nth);
        return $pos === null ? null : self::length(\substr($haystack, 0, $pos));
    }
    /**
     * Returns position in characters of $nth occurence of $needle in $haystack or null if the needle was not found.
     */
    private static function pos(string $haystack, string $needle, int $nth = 1) : ?int
    {
        if (!$nth) {
            return null;
        } elseif ($nth > 0) {
            if ($needle === '') {
                return 0;
            }
            $pos = 0;
            while (($pos = \strpos($haystack, $needle, $pos)) !== \false && --$nth) {
                $pos++;
            }
        } else {
            $len = \strlen($haystack);
            if ($needle === '') {
                return $len;
            }
            $pos = $len - 1;
            while (($pos = \strrpos($haystack, $needle, $pos - $len)) !== \false && ++$nth) {
                $pos--;
            }
        }
        return \RectorPrefix20211020\Nette\Utils\Helpers::falseToNull($pos);
    }
    /**
     * Splits a string into array by the regular expression.
     * Argument $flag takes same arguments as preg_split(), but PREG_SPLIT_DELIM_CAPTURE is set by default.
     * @param string $subject
     * @param string $pattern
     * @param int $flags
     */
    public static function split($subject, $pattern, $flags = 0) : array
    {
        return self::pcre('preg_split', [$pattern, $subject, -1, $flags | \PREG_SPLIT_DELIM_CAPTURE]);
    }
    /**
     * Checks if given string matches a regular expression pattern and returns an array with first found match and each subpattern.
     * Argument $flag takes same arguments as function preg_match().
     * @param string $subject
     * @param string $pattern
     * @param int $flags
     * @param int $offset
     */
    public static function match($subject, $pattern, $flags = 0, $offset = 0) : ?array
    {
        if ($offset > \strlen($subject)) {
            return null;
        }
        return self::pcre('preg_match', [$pattern, $subject, &$m, $flags, $offset]) ? $m : null;
    }
    /**
     * Finds all occurrences matching regular expression pattern and returns a two-dimensional array.
     * Argument $flag takes same arguments as function preg_match_all(), but PREG_SET_ORDER is set by default.
     * @param string $subject
     * @param string $pattern
     * @param int $flags
     * @param int $offset
     */
    public static function matchAll($subject, $pattern, $flags = 0, $offset = 0) : array
    {
        if ($offset > \strlen($subject)) {
            return [];
        }
        self::pcre('preg_match_all', [$pattern, $subject, &$m, $flags & \PREG_PATTERN_ORDER ? $flags : $flags | \PREG_SET_ORDER, $offset]);
        return $m;
    }
    /**
     * Replaces all occurrences matching regular expression $pattern which can be string or array in the form `pattern => replacement`.
     * @param  string|array  $pattern
     * @param  string|callable  $replacement
     * @param string $subject
     * @param int $limit
     */
    public static function replace($subject, $pattern, $replacement = '', $limit = -1) : string
    {
        if (\is_object($replacement) || \is_array($replacement)) {
            if (!\is_callable($replacement, \false, $textual)) {
                throw new \RectorPrefix20211020\Nette\InvalidStateException("Callback '{$textual}' is not callable.");
            }
            return self::pcre('preg_replace_callback', [$pattern, $replacement, $subject, $limit]);
        } elseif (\is_array($pattern) && \is_string(\key($pattern))) {
            $replacement = \array_values($pattern);
            $pattern = \array_keys($pattern);
        }
        return self::pcre('preg_replace', [$pattern, $replacement, $subject, $limit]);
    }
    /** @internal
     * @param string $func
     * @param mixed[] $args */
    public static function pcre($func, $args)
    {
        $res = \RectorPrefix20211020\Nette\Utils\Callback::invokeSafe($func, $args, function (string $message) use($args) : void {
            // compile-time error, not detectable by preg_last_error
            throw new \RectorPrefix20211020\Nette\Utils\RegexpException($message . ' in pattern: ' . \implode(' or ', (array) $args[0]));
        });
        if (($code = \preg_last_error()) && ($res === null || !\in_array($func, ['preg_filter', 'preg_replace_callback', 'preg_replace'], \true))) {
            throw new \RectorPrefix20211020\Nette\Utils\RegexpException((\RectorPrefix20211020\Nette\Utils\RegexpException::MESSAGES[$code] ?? 'Unknown error') . ' (pattern: ' . \implode(' or ', (array) $args[0]) . ')', $code);
        }
        return $res;
    }
}
