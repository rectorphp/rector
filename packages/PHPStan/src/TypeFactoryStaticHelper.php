<?php declare(strict_types=1);

namespace Rector\PHPStan;

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
            if ($type instanceof Type) {
                $objectTypes[] = $type;
            } else {
                $objectTypes[] = new ObjectType($type);
            }
        }

        // this is needed to prevent missing broker static fatal error, for tests with missing class
        $unionTypeClassReflection = new ReflectionClass(UnionType::class);

        /** @var UnionType $unionType */
        $unionType = $unionTypeClassReflection->newInstanceWithoutConstructor();

        $privatesAccessor = new PrivatesAccessor();
        $privatesAccessor->setPrivateProperty($unionType, 'types', $objectTypes);

        return $unionType;
    }
}
