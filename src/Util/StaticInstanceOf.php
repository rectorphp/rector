<?php

declare(strict_types=1);

namespace Rector\Core\Util;

/**
 * @see \Rector\Core\Tests\Util\StaticInstanceOfTest
 */
final class StaticInstanceOf
{
    /**
     * @param class-string[] $array
     */
    public static function isOneOf(?object $object, array $array): bool
    {
        if ($object === null) {
            return false;
        }

        foreach ($array as $classLike) {
            if (is_a($object, $classLike, true)) {
                return true;
            }
        }

        return false;
    }
}
