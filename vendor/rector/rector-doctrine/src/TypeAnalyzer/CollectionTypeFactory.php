<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\TypeAnalyzer;

use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericObjectType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
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
