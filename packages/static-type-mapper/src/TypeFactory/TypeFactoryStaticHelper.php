<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\TypeFactory;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use ReflectionClass;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class TypeFactoryStaticHelper
{
    /**
     * @param string[]|Type[] $types
     */
    public static function createUnionObjectType(array $types): UnionType
    {
        $objectTypes = [];
        foreach ($types as $type) {
            $objectTypes[] = $type instanceof Type ? $type : new ObjectType($type);
        }

        // this is needed to prevent missing broker static fatal error, for tests with missing class
        $reflectionClass = new ReflectionClass(UnionType::class);

        /** @var UnionType $unionType */
        $unionType = $reflectionClass->newInstanceWithoutConstructor();

        $privatesAccessor = new PrivatesAccessor();
        $privatesAccessor->setPrivateProperty($unionType, 'types', $objectTypes);

        return $unionType;
    }
}
