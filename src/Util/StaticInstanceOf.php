<?php

declare(strict_types=1);

namespace Rector\Core\Util;

/**
 * @see \Rector\Core\Tests\Util\StaticInstanceOfTest
 */
final class StaticInstanceOf
{
    /**
     * @param string|object|null $element
     * @param class-string[] $array
     */
    public static function isOneOf($element, array $array): bool
    {
        if ($element === null) {
            return false;
        }

        foreach ($array as $singleArray) {
            if (is_a($element, $singleArray, true)) {
                return true;
            }
        }

        return false;
    }
}
