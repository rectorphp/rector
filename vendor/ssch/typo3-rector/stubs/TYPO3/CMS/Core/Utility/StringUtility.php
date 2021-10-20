<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\StringUtility')) {
    return;
}
class StringUtility
{
    public static function beginsWith($haystack, $needle)
    {
        // Sanitize $haystack and $needle
        if (\is_array($haystack) || \is_object($haystack) || null === $haystack || (string) $haystack != $haystack) {
            throw new \InvalidArgumentException('$haystack can not be interpreted as string', 1347135546);
        }
        if (\is_array($needle) || \is_object($needle) || (string) $needle != $needle || \strlen($needle) < 1) {
            throw new \InvalidArgumentException('$needle can not be interpreted as string or has zero length', 1347135547);
        }
        $haystack = (string) $haystack;
        $needle = (string) $needle;
        return '' !== $needle && 0 === \strpos($haystack, $needle);
    }
    public static function uniqueList($list)
    {
        return [];
    }
}
