<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\TypeComparator;

use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantIntegerType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower;
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
        $mainKeyType = $this->narrowArrayKeysUnionType($mainKeyType);
        $checkedKeyType = $this->narrowArrayKeysUnionType($checkedKeyType);
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
    /**
     * Native array order can be treated as mixed type
     */
    private function narrowArrayKeysUnionType(Type $type) : Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        foreach ($type->getTypes() as $key => $unionedType) {
            if (!$unionedType instanceof ConstantIntegerType) {
                return $type;
            }
            if ($key === $unionedType->getValue()) {
                continue;
            }
            return $type;
        }
        return new MixedType();
    }
}
