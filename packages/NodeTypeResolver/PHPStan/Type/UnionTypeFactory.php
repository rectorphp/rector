<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class UnionTypeFactory
{
    public function createNullableUnionType(Type $type) : UnionType
    {
        if ($type instanceof UnionType) {
            $item0Unpacked = $type->getTypes();
            // Adding a UnionType into a new UnionType throws an exception so we need to "unpack" the types
            return new UnionType(\array_merge($item0Unpacked, [new NullType()]));
        }
        return new UnionType([$type, new NullType()]);
    }
}
