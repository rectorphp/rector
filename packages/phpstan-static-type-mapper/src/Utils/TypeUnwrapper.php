<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\Utils;

use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;

final class TypeUnwrapper
{
    /**
     * @var UnionTypeFactory
     */
    private $unionTypeFactory;

    public function __construct(UnionTypeFactory $unionTypeFactory)
    {
        $this->unionTypeFactory = $unionTypeFactory;
    }

    /**
     * E.g. null|ClassType → ClassType
     */
    public function unwrapNullableType(Type $type): Type
    {
        return TypeCombinator::removeNull($type);
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

        return $this->unionTypeFactory->createUnionObjectType($unionedTypesWithoutNullType);
    }
}
