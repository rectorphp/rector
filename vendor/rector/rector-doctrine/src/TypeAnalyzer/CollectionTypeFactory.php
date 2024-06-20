<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypeAnalyzer;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class CollectionTypeFactory
{
    public function createType(FullyQualifiedObjectType $fullyQualifiedObjectType) : GenericObjectType
    {
        $genericTypes = [new IntegerType(), $fullyQualifiedObjectType];
        return new GenericObjectType('Doctrine\\Common\\Collections\\Collection', $genericTypes);
    }
}
