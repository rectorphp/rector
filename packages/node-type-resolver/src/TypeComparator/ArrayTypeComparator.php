<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypeComparator;

use PHPStan\Type\ArrayType;

final class ArrayTypeComparator
{
    public function isSubtype(ArrayType $checkedType, ArrayType $mainType): bool
    {
        $checkedKeyType = $checkedType->getKeyType();
        $mainKeyType = $mainType->getKeyType();

        dump($mainKeyType);
        dump($checkedKeyType);

        if (! $checkedKeyType->isSuperTypeOf($mainKeyType)->yes()) {
            return false;
        }

        dump($mainKeyType);
        dump($checkedKeyType);
        die;

        return false;
    }
}
