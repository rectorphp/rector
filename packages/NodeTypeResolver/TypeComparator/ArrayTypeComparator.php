<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeComparator;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower;
/**
 * @see \Rector\Tests\NodeTypeResolver\TypeComparator\ArrayTypeComparatorTest
 */
final class ArrayTypeComparator
{
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower
     */
    private $unionTypeCommonTypeNarrower;
    public function __construct(UnionTypeCommonTypeNarrower $unionTypeCommonTypeNarrower)
    {
        $this->unionTypeCommonTypeNarrower = $unionTypeCommonTypeNarrower;
    }
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
        if ($checkedItemType instanceof UnionType) {
            $checkedItemType = $this->unionTypeCommonTypeNarrower->narrowToGenericClassStringType($checkedItemType);
        }
        $mainItemType = $mainType->getItemType();
        if ($mainItemType instanceof UnionType) {
            $mainItemType = $this->unionTypeCommonTypeNarrower->narrowToGenericClassStringType($mainItemType);
        }
        return $checkedItemType->isSuperTypeOf($mainItemType)->yes();
    }
}
