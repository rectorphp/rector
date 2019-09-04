<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Exception\ShouldNotHappenException;
use Rector\PHPStan\TypeFactoryStaticHelper;

final class TypeFactory
{
    /**
     * @param string[] $allTypes
     * @return ObjectType|UnionType
     */
    public function createObjectTypeOrUnionType(array $allTypes): Type
    {
        if (count($allTypes) === 1) {
            return new ObjectType($allTypes[0]);
        }

        if (count($allTypes) > 1) {
            // keep original order, UnionType internally overrides it → impossible to get first type back, e.g. class over interface
            return TypeFactoryStaticHelper::createUnionObjectType($allTypes);
        }

        throw new ShouldNotHappenException();
    }
}
