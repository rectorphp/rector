<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeComparator;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
/**
 * @see \Rector\Tests\NodeTypeResolver\TypeComparator\ArrayTypeComparatorTest
 */
final class ArrayTypeComparator
{
    public function isSubtype(ArrayType $checkedType, ArrayType $mainType) : bool
    {
        if (!$checkedType instanceof ConstantArrayType && !$mainType instanceof ConstantArrayType) {
            return $mainType->isSuperTypeOf($checkedType)->yes();
        }
        $checkedKeyType = $checkedType->getKeyType();
        $mainKeyType = $mainType->getKeyType();
        if (!$mainKeyType instanceof MixedType && $mainKeyType->isSuperTypeOf($checkedKeyType)->yes()) {
            return \true;
        }
        $checkedItemType = $checkedType->getItemType();
        $mainItemType = $mainType->getItemType();
        return $checkedItemType->isSuperTypeOf($mainItemType)->yes();
    }
}
