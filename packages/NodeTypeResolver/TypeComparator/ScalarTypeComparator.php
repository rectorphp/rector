<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypeComparator;

use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

/**
 * @see \Rector\Tests\NodeTypeResolver\TypeComparator\ScalarTypeComparatorTest
 */
final class ScalarTypeComparator
{
    public function areEqualScalar(Type $firstType, Type $secondType): bool
    {
        if ($firstType instanceof StringType && $secondType instanceof StringType) {
            // prevents "class-string" vs "string"
            $firstTypeClass = $firstType::class;
            $secondTypeClass = $secondType::class;

            return $firstTypeClass === $secondTypeClass;
        }

        if ($firstType instanceof IntegerType && $secondType instanceof IntegerType) {
            return true;
        }

        if ($firstType instanceof FloatType && $secondType instanceof FloatType) {
            return true;
        }

        if (! $firstType instanceof BooleanType) {
            return false;
        }

        return $secondType instanceof BooleanType;
    }
}
