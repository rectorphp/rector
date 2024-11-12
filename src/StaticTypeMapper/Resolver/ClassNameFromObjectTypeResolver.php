<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Resolver;

use PHPStan\Type\Type;
final class ClassNameFromObjectTypeResolver
{
    public static function resolve(Type $type) : ?string
    {
        $objectClassNames = $type->getObjectClassNames();
        if (\count($objectClassNames) !== 1) {
            return null;
        }
        return $objectClassNames[0];
    }
}
