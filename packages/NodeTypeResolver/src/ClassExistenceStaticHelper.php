<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

final class ClassExistenceStaticHelper
{
    public static function doesClassLikeExist(string $classLike): bool
    {
        return class_exists($classLike) || interface_exists($classLike) || trait_exists($classLike);
    }
}
