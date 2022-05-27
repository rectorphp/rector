<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\Utils;

use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
final class TypeUnwrapper
{
    /**
     * E.g. null|ClassType → ClassType
     */
    public function unwrapNullableType(Type $type) : Type
    {
        return TypeCombinator::removeNull($type);
    }
    public function unwrapFirstObjectTypeFromUnionType(Type $type) : Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        foreach ($type->getTypes() as $unionedType) {
            if (!$unionedType instanceof TypeWithClassName) {
                continue;
            }
            return $unionedType;
        }
        return $type;
    }
    public function removeNullTypeFromUnionType(UnionType $unionType) : UnionType
    {
        $unionedTypesWithoutNullType = [];
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof UnionType) {
                continue;
            }
            $unionedTypesWithoutNullType[] = $type;
        }
        return new UnionType($unionedTypesWithoutNullType);
    }
}
