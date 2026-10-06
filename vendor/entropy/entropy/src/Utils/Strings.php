<?php

declare (strict_types=1);
namespace RectorPrefix202610\Entropy\Utils;

use RectorPrefix202610\Entropy\Attribute\RelatedTest;
use RectorPrefix202610\Entropy\Tests\Utils\StringsTest;
/**
 * @api to be used outside
 * @see \Entropy\Tests\Utils\StringsTest
 */
final class Strings
{
    public static function webalize(string $text): string
    {
        $text = (string) preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
        $text = trim($text, '-');
        return strtolower($text);
    }
    public static function contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== \false;
    }
    /**
     * Returns the part of $haystack after the $nth occurrence of $needle,
     * or null when the needle is not found. Negative $nth counts from the end.
     */
    public static function after(string $haystack, string $needle, int $nth = 1): ?string
    {
        if ($nth === 0) {
            return null;
        }
        if ($needle === '') {
            return (string) substr($haystack, $nth > 0 ? 0 : strlen($haystack));
        }
        if ($nth > 0) {
            $offset = 0;
            $position = \false;
            for ($i = 0; $i < $nth; ++$i) {
                $position = strpos($haystack, $needle, $offset);
                if ($position === \false) {
                    return null;
                }
                $offset = $position + strlen($needle);
            }
        } else {
            $end = strlen($haystack);
            $position = \false;
            for ($i = 0; $i < -$nth; ++$i) {
                $position = strrpos((string) substr($haystack, 0, $end), $needle);
                if ($position === \false) {
                    return null;
                }
                $end = $position;
            }
        }
        return (string) substr($haystack, $position + strlen($needle));
    }
    /**
     * Returns the part of $haystack before the $nth occurrence of $needle,
     * or null when the needle is not found. Negative $nth counts from the end.
     */
    public static function before(string $haystack, string $needle, int $nth = 1): ?string
    {
        if ($nth === 0) {
            return null;
        }
        if ($needle === '') {
            return (string) substr($haystack, 0, $nth > 0 ? 0 : strlen($haystack));
        }
        if ($nth > 0) {
            $offset = 0;
            $position = \false;
            for ($i = 0; $i < $nth; ++$i) {
                $position = strpos($haystack, $needle, $offset);
                if ($position === \false) {
                    return null;
                }
                $offset = $position + strlen($needle);
            }
        } else {
            $end = strlen($haystack);
            $position = \false;
            for ($i = 0; $i < -$nth; ++$i) {
                $position = strrpos((string) substr($haystack, 0, $end), $needle);
                if ($position === \false) {
                    return null;
                }
                $end = $position;
            }
        }
        return (string) substr($haystack, 0, $position);
    }
}
