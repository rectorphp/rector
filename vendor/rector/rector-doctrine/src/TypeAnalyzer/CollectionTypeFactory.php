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
    public function createType(FullyQualifiedObjectType $fullyQualifiedObjectType) : UnionType
    {
        $genericObjectType = $this->createGenericObjectType($fullyQualifiedObjectType);
        $arrayType = new ArrayType(new MixedType(), $fullyQualifiedObjectType);
        return new UnionType([$genericObjectType, $arrayType]);
    }
    private function createGenericObjectType(FullyQualifiedObjectType $fullyQualifiedObjectType) : GenericObjectType
    {
        $genericTypes = [new IntegerType(), $fullyQualifiedObjectType];
        return new GenericObjectType('Doctrine\\Common\\Collections\\Collection', $genericTypes);
    }
}
