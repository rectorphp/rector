<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypeAnalyzer;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
final class CollectionTypeFactory
{
    public function createType(ObjectType $objectType) : GenericObjectType
    {
        $genericTypes = [new IntegerType(), $objectType];
        return new GenericObjectType('Doctrine\\Common\\Collections\\Collection', $genericTypes);
    }
}
