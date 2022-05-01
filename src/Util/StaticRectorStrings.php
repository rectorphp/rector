<?php

declare (strict_types=1);
namespace Rector\Core\Util;

use RectorPrefix20220501\Nette\Utils\Strings;
final class StaticRectorStrings
{
    /**
     * @var string
     * @see https://regex101.com/r/4w2of2/2
     */
    private const CAMEL_CASE_SPLIT_REGEX = '#([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)#';
    /**
     * From: utf-8 → to: UTF_8
     */
    public static function camelCaseToUnderscore(string $input) : string
    {
        if ($input === \strtolower($input)) {
            return $input;
        }
        $matches = \RectorPrefix20220501\Nette\Utils\Strings::matchAll($input, self::CAMEL_CASE_SPLIT_REGEX);
        $parts = [];
        foreach ($matches as $match) {
            $parts[] = $match[0] === \strtoupper($match[0]) ? \strtolower($match[0]) : \lcfirst($match[0]);
        }
        return \implode('_', $parts);
    }
    /**
     * @param string[] $array
     */
    public static function isInArrayInsensitive(string $checkedItem, array $array) : bool
    {
        $checkedItem = \strtolower($checkedItem);
        foreach ($array as $singleArray) {
            if (\strtolower($singleArray) === $checkedItem) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $prefixesToRemove
     */
    public static function removePrefixes(string $value, array $prefixesToRemove) : string
    {
        foreach ($prefixesToRemove as $prefixToRemove) {
            if (\strncmp($value, $prefixToRemove, \strlen($prefixToRemove)) === 0) {
                $value = \RectorPrefix20220501\Nette\Utils\Strings::substring($value, \RectorPrefix20220501\Nette\Utils\Strings::length($prefixToRemove));
            }
        }
        return $value;
    }
    /**
     * @param string[] $suffixesToRemove
     */
    public static function removeSuffixes(string $value, array $suffixesToRemove) : string
    {
        foreach ($suffixesToRemove as $suffixToRemove) {
            if (\substr_compare($value, $suffixToRemove, -\strlen($suffixToRemove)) === 0) {
                $value = \RectorPrefix20220501\Nette\Utils\Strings::substring($value, 0, -\RectorPrefix20220501\Nette\Utils\Strings::length($suffixToRemove));
            }
        }
        return $value;
    }
}
