<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\Utils;

use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\TypeFactory\TypeFactoryStaticHelper;

final class TypeUnwrapper
{
    /**
     * E.g. null|ClassType → ClassType
     */
    public function unwrapNullableType(Type $type): Type
    {
        if (! $type instanceof UnionType) {
            return $type;
        }

        if (count($type->getTypes()) !== 2) {
            return $type;
        }

        if (! $type->isSuperTypeOf(new NullType())->yes()) {
            return $type;
        }

        foreach ($type->getTypes() as $unionedType) {
            if ($unionedType instanceof NullType) {
                continue;
            }

            return $unionedType;
        }

        return $type;
    }

    public function unwrapFirstObjectTypeFromUnionType(Type $type): Type
    {
        if (! $type instanceof UnionType) {
            return $type;
        }

        foreach ($type->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                continue;
            }

            return $unionedType;
        }

        return $type;
    }

    /**
     * @return Type|UnionType
     */
    public function removeNullTypeFromUnionType(UnionType $unionType): Type
    {
        $unionedTypesWithoutNullType = [];

        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof UnionType) {
                continue;
            }

            $unionedTypesWithoutNullType[] = $type;
        }

        return TypeFactoryStaticHelper::createUnionObjectType($unionedTypesWithoutNullType);
    }
}
