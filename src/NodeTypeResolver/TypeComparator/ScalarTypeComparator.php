<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeComparator;

use PHPStan\Type\Type;
/**
 * @see \Rector\Tests\NodeTypeResolver\TypeComparator\ScalarTypeComparatorTest
 */
final class ScalarTypeComparator
{
    public function areEqualScalar(Type $firstType, Type $secondType) : bool
    {
        if ($firstType->isString()->yes() && $secondType->isString()->yes()) {
            // prevents "class-string" vs "string"
            $firstTypeClass = \get_class($firstType);
            $secondTypeClass = \get_class($secondType);
            return $firstTypeClass === $secondTypeClass;
        }
        if ($firstType->isInteger()->yes() && $secondType->isInteger()->yes()) {
            // prevents "int<min, max>" vs "int"
            $firstTypeClass = \get_class($firstType);
            $secondTypeClass = \get_class($secondType);
            return $firstTypeClass === $secondTypeClass;
        }
        if ($firstType->isFloat()->yes() && $secondType->isFloat()->yes()) {
            return \true;
        }
        if (!$firstType->isBoolean()->yes()) {
            return \false;
        }
        return $secondType->isBoolean()->yes();
    }
    /**
     * E.g. first is string, second is bool
     */
    public function areDifferentScalarTypes(Type $firstType, Type $secondType) : bool
    {
        if (!$firstType->isScalar()->yes()) {
            return \false;
        }
        if (!$secondType->isScalar()->yes()) {
            return \false;
        }
        // treat class-string and string the same
        if ($firstType->isString()->yes() && $secondType->isString()->yes()) {
            return \false;
        }
        if ($firstType->isInteger()->yes() && $secondType->isInteger()->yes()) {
            return \false;
        }
        if (!$firstType->isString()->yes()) {
            return \get_class($firstType) !== \get_class($secondType);
        }
        if (!$secondType->isClassString()->yes()) {
            return \get_class($firstType) !== \get_class($secondType);
        }
        return \false;
    }
}
