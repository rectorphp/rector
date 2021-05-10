<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeComparator;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower;
/**
 * @see \Rector\Tests\NodeTypeResolver\TypeComparator\ArrayTypeComparatorTest
 */
final class ArrayTypeComparator
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower
     */
    private $unionTypeCommonTypeNarrower;
    public function __construct(\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower $unionTypeCommonTypeNarrower)
    {
        $this->unionTypeCommonTypeNarrower = $unionTypeCommonTypeNarrower;
    }
    public function isSubtype(\PHPStan\Type\ArrayType $checkedType, \PHPStan\Type\ArrayType $mainType) : bool
    {
        if (!$checkedType instanceof \PHPStan\Type\Constant\ConstantArrayType && !$mainType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return $mainType->isSuperTypeOf($checkedType)->yes();
        }
        $checkedKeyType = $checkedType->getKeyType();
        $mainKeyType = $mainType->getKeyType();
        $mainKeyType = $this->narrowArrayKeysUnionType($mainKeyType);
        $checkedKeyType = $this->narrowArrayKeysUnionType($checkedKeyType);
        if (!$mainKeyType instanceof \PHPStan\Type\MixedType && $mainKeyType->isSuperTypeOf($checkedKeyType)->yes()) {
            return \true;
        }
        $checkedItemType = $checkedType->getItemType();
        if ($checkedItemType instanceof \PHPStan\Type\UnionType) {
            $checkedItemType = $this->unionTypeCommonTypeNarrower->narrowToGenericClassStringType($checkedItemType);
        }
        $mainItemType = $mainType->getItemType();
        if ($mainItemType instanceof \PHPStan\Type\UnionType) {
            $mainItemType = $this->unionTypeCommonTypeNarrower->narrowToGenericClassStringType($mainItemType);
        }
        return $checkedItemType->isSuperTypeOf($mainItemType)->yes();
    }
    /**
     * Native array order can be treated as mixed type
     */
    private function narrowArrayKeysUnionType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return $type;
        }
        foreach ($type->getTypes() as $key => $unionedType) {
            if (!$unionedType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                return $type;
            }
            if ($key === $unionedType->getValue()) {
                continue;
            }
            return $type;
        }
        return new \PHPStan\Type\MixedType();
    }
}
