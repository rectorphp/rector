<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\PhpDoc;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class CollectionTypeFactory
{
    public function createType(FullyQualifiedObjectType $fullyQualifiedObjectType): UnionType
    {
        $genericType = $this->createGenericObjectType($fullyQualifiedObjectType);
        $arrayType = new ArrayType(new MixedType(), $fullyQualifiedObjectType);

        return new UnionType([$genericType, $arrayType]);
    }

    private function createGenericObjectType(FullyQualifiedObjectType $fullyQualifiedObjectType): Type
    {
        $genericTypes = [new IntegerType(), $fullyQualifiedObjectType];

        return new GenericObjectType('Doctrine\Common\Collections\Collection', $genericTypes);
    }
}
