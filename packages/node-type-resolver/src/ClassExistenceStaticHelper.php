<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

/**
 * @deprecated This must be replaced by ReflectionProvider from PHPStan, that knows about classes and autolaods them statically
 */
final class ClassExistenceStaticHelper
{
    public static function doesClassLikeExist(string $classLike): bool
    {
        if (class_exists($classLike)) {
            return true;
        }

        if (interface_exists($classLike)) {
            return true;
        }

        return trait_exists($classLike);
    }
}
