<?php

declare(strict_types=1);

namespace Rector\Core\Util;

use Nette\Utils\Strings;

/**
 * @see \Rector\Core\Tests\Util\RectorStringsTest
 */
final class StaticRectorStrings
{
    /**
     * @param string[] $array
     */
    public static function isInArrayInsensitive(string $checkedItem, array $array): bool
    {
        foreach ($array as $item) {
            if (Strings::lower($item) === Strings::lower($checkedItem)) {
                return true;
            }
        }

        return false;
    }

    public static function camelCaseToDashes(string $input): string
    {
        return self::camelCaseToGlue($input, '-');
    }

    public static function camelCaseToSlashes(string $input): string
    {
        return self::camelCaseToGlue($input, '/');
    }

    public static function camelCaseToUnderscore(string $input): string
    {
        return self::camelCaseToGlue($input, '_');
    }

    public static function underscoreToCamelCase(string $input): string
    {
        $nameParts = explode('_', $input);
        $camelCase = '';

        foreach ($nameParts as $namePart) {
            $camelCase .= ucfirst($namePart);
        }

        return $camelCase;
    }

    /**
     * @param string[] $prefixesToRemove
     */
    public static function removePrefixes(string $value, array $prefixesToRemove): string
    {
        foreach ($prefixesToRemove as $prefixToRemove) {
            if (Strings::startsWith($value, $prefixToRemove)) {
                $value = Strings::substring($value, Strings::length($prefixToRemove));
            }
        }

        return $value;
    }

    /**
     * @param string[] $suffixesToRemove
     */
    public static function removeSuffixes(string $value, array $suffixesToRemove): string
    {
        foreach ($suffixesToRemove as $prefixToRemove) {
            if (Strings::endsWith($value, $prefixToRemove)) {
                $value = Strings::substring($value, 0, -Strings::length($prefixToRemove));
            }
        }

        return $value;
    }

    private static function camelCaseToGlue(string $input, string $glue): string
    {
        if ($input === strtolower($input)) {
            return $input;
        }

        $matches = Strings::matchAll($input, '#([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)#');
        $parts = [];
        foreach ($matches as $match) {
            $parts[] = $match[0] === strtoupper($match[0]) ? strtolower($match[0]) : lcfirst($match[0]);
        }

        return implode($glue, $parts);
    }
}
