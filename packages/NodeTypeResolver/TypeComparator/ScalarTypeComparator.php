<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeComparator;

use PHPStan\Type\BooleanType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
/**
 * @see \Rector\Tests\NodeTypeResolver\TypeComparator\ScalarTypeComparatorTest
 */
final class ScalarTypeComparator
{
    public function areEqualScalar(\PHPStan\Type\Type $firstType, \PHPStan\Type\Type $secondType) : bool
    {
        if ($firstType instanceof \PHPStan\Type\StringType && $secondType instanceof \PHPStan\Type\StringType) {
            // prevents "class-string" vs "string"
            $firstTypeClass = \get_class($firstType);
            $secondTypeClass = \get_class($secondType);
            return $firstTypeClass === $secondTypeClass;
        }
        if ($firstType instanceof \PHPStan\Type\IntegerType && $secondType instanceof \PHPStan\Type\IntegerType) {
            return \true;
        }
        if ($firstType instanceof \PHPStan\Type\FloatType && $secondType instanceof \PHPStan\Type\FloatType) {
            return \true;
        }
        if (!$firstType instanceof \PHPStan\Type\BooleanType) {
            return \false;
        }
        return $secondType instanceof \PHPStan\Type\BooleanType;
    }
    /**
     * E.g. first is string, second is bool
     */
    public function areDifferentScalarTypes(\PHPStan\Type\Type $firstType, \PHPStan\Type\Type $secondType) : bool
    {
        if (!$this->isScalarType($firstType)) {
            return \false;
        }
        if (!$this->isScalarType($secondType)) {
            return \false;
        }
        // treat class-string and string the same
        if ($firstType instanceof \PHPStan\Type\ClassStringType && $secondType instanceof \PHPStan\Type\StringType) {
            return \false;
        }
        if (!$firstType instanceof \PHPStan\Type\StringType) {
            return \get_class($firstType) !== \get_class($secondType);
        }
        if (!$secondType instanceof \PHPStan\Type\ClassStringType) {
            return \get_class($firstType) !== \get_class($secondType);
        }
        return \false;
    }
    private function isScalarType(\PHPStan\Type\Type $type) : bool
    {
        if ($type instanceof \PHPStan\Type\StringType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\FloatType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\IntegerType) {
            return \true;
        }
        return $type instanceof \PHPStan\Type\BooleanType;
    }
}
