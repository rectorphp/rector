<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class StaticTypeAnalyzer
{
    /**
     * @param Type[] $types
     */
    public function areTypesAlwaysTruable(array $types): bool
    {
        if ($types === []) {
            return false;
        }

        foreach ($types as $type) {
            if ($type instanceof ConstantArrayType) {
                continue;
            }

            if ($type instanceof ArrayType) {
                return false;
            }

            if ($this->isNullable($type)) {
                return false;
            }

            if ($type instanceof MixedType) {
                return false;
            }

            // always trueish
            if ($type instanceof ObjectType) {
                continue;
            }

            if ($type instanceof ConstantScalarType && ! $type instanceof NullType) {
                if (! $type->getValue()) {
                    return false;
                }

                continue;
            }

            if ($this->isScalarType($type)) {
                return false;
            }
        }

        return true;
    }

    public function isNullable(Type $type): bool
    {
        if (! $type instanceof UnionType) {
            return false;
        }

        foreach ($type->getTypes() as $unionedType) {
            if ($unionedType instanceof NullType) {
                return true;
            }
        }

        return false;
    }

    private function isScalarType(Type $type): bool
    {
        if ($type instanceof NullType) {
            return true;
        }

        return $type instanceof BooleanType || $type instanceof StringType || $type instanceof IntegerType || $type instanceof FloatType;
    }
}
