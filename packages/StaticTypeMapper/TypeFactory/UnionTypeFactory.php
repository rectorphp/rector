<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\TypeFactory;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use ReflectionClass;
use RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
final class UnionTypeFactory
{
    /**
     * @param string[]|Type[] $types
     */
    public function createUnionObjectType(array $types) : \PHPStan\Type\UnionType
    {
        $objectTypes = [];
        foreach ($types as $type) {
            $objectTypes[] = $type instanceof \PHPStan\Type\Type ? $type : new \PHPStan\Type\ObjectType($type);
        }
        // this is needed to prevent missing broker static fatal error, for tests with missing class
        $reflectionClass = new \ReflectionClass(\PHPStan\Type\UnionType::class);
        /** @var UnionType $unionType */
        $unionType = $reflectionClass->newInstanceWithoutConstructor();
        $privatesAccessor = new \RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor();
        $privatesAccessor->setPrivateProperty($unionType, 'types', $objectTypes);
        return $unionType;
    }
}
