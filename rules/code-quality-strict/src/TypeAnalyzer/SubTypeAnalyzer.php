<?php

declare(strict_types=1);

namespace Rector\CodeQualityStrict\TypeAnalyzer;

use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;

final class SubTypeAnalyzer
{
    public function isObjectSubType(Type $checkedType, Type $mainType): bool
    {
        if (! $checkedType instanceof TypeWithClassName) {
            return false;
        }

        if (! $mainType instanceof TypeWithClassName) {
            return false;
        }

        $checkedClassName = $checkedType instanceof ShortenedObjectType ? $checkedType->getFullyQualifiedName() : $checkedType->getClassName();

        $mainClassName = $mainType instanceof ShortenedObjectType ? $mainType->getFullyQualifiedName() : $mainType->getClassName();

        if (is_a($checkedClassName, $mainClassName, true)) {
            return true;
        }

        // child of every object
        return $mainClassName === 'stdClass';
    }
}
