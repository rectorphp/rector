<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class NamespaceBeforeClassNameResolver
{
    public function resolve(FullyQualifiedObjectType $fullyQualifiedObjectType): string
    {
        $className = $fullyQualifiedObjectType->getClassName();
        $shortName = $fullyQualifiedObjectType->getShortName();
        return $className === $shortName ? '' : substr($className, 0, -strlen($shortName) - 1);
    }
}
