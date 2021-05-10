<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\Utils;

use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;
final class TypeUnwrapper
{
    /**
     * @var \Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory
     */
    private $unionTypeFactory;
    public function __construct(\Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory $unionTypeFactory)
    {
        $this->unionTypeFactory = $unionTypeFactory;
    }
    /**
     * E.g. null|ClassType → ClassType
     */
    public function unwrapNullableType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeCombinator::removeNull($type);
    }
    public function unwrapFirstObjectTypeFromUnionType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return $type;
        }
        foreach ($type->getTypes() as $unionedType) {
            if (!$unionedType instanceof \PHPStan\Type\TypeWithClassName) {
                continue;
            }
            return $unionedType;
        }
        return $type;
    }
    public function removeNullTypeFromUnionType(\PHPStan\Type\UnionType $unionType) : \PHPStan\Type\UnionType
    {
        $unionedTypesWithoutNullType = [];
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof \PHPStan\Type\UnionType) {
                continue;
            }
            $unionedTypesWithoutNullType[] = $type;
        }
        return $this->unionTypeFactory->createUnionObjectType($unionedTypesWithoutNullType);
    }
}
