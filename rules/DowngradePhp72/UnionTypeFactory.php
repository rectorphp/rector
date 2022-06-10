<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72;

use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class UnionTypeFactory
{
    public function createNullableUnionType(Type $type): UnionType
    {
        if ($type instanceof UnionType) {
            // Adding a UnionType into a new UnionType throws an exception so we need to "unpack" the types
            return new UnionType([...$type->getTypes(), new NullType()]);
        }

        return new UnionType([$type, new NullType()]);
    }
}
