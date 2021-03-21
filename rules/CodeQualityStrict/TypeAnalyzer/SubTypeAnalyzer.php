<?php

declare(strict_types=1);

namespace Rector\CodeQualityStrict\TypeAnalyzer;

use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;

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

        // parent type to all objects
        if ($mainType->getClassName() === 'stdClass') {
            return true;
        }

        return $mainType->isSuperTypeOf($checkedType)
            ->yes();
    }
}
