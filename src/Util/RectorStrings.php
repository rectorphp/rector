<?php

declare(strict_types=1);

namespace Rector\Util;

use Nette\Utils\Strings;
use Symfony\Component\Console\Input\StringInput;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * @see \Rector\Tests\Util\RectorStringsTest
 */
final class RectorStrings
{
    /**
     * @return string[]
     */
    public static function splitCommandToItems(string $command): array
    {
        $privatesCaller = new PrivatesCaller();

        return $privatesCaller->callPrivateMethod(new StringInput(''), 'tokenize', $command);
    }

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
        $matches = Strings::matchAll($input, '#([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)#');

        $parts = [];
        foreach ($matches as $match) {
            $parts[] = $match[0] === strtoupper($match[0]) ? strtolower($match[0]) : lcfirst($match[0]);
        }

        return implode($glue, $parts);
    }
}
