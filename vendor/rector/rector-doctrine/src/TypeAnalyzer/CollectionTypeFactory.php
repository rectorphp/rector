<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class CollectionTypeFactory
{
    public function createType(\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : \PHPStan\Type\UnionType
    {
        $genericType = $this->createGenericObjectType($fullyQualifiedObjectType);
        $arrayType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), $fullyQualifiedObjectType);
        return new \PHPStan\Type\UnionType([$genericType, $arrayType]);
    }
    private function createGenericObjectType(\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : \PHPStan\Type\Generic\GenericObjectType
    {
        $genericTypes = [new \PHPStan\Type\IntegerType(), $fullyQualifiedObjectType];
        return new \PHPStan\Type\Generic\GenericObjectType('Doctrine\\Common\\Collections\\Collection', $genericTypes);
    }
}
