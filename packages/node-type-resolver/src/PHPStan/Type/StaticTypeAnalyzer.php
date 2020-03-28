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
    public function isAlwaysTruableType(Type $type): bool
    {
        if ($type instanceof MixedType) {
            return false;
        }

        if ($type instanceof ConstantArrayType) {
            return true;
        }

        if ($type instanceof ArrayType) {
            return false;
        }

        if ($this->isNullable($type)) {
            return false;
        }

        // always trueish
        if ($type instanceof ObjectType) {
            return true;
        }

        if ($type instanceof ConstantScalarType && ! $type instanceof NullType) {
            return (bool) $type->getValue();
        }

        if ($this->isScalarType($type)) {
            return false;
        }

        return $this->isAlwaysTruableUnionType($type);
    }

    private function isNullable(Type $type): bool
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

    private function isAlwaysTruableUnionType(Type $type): bool
    {
        if (! $type instanceof UnionType) {
            return false;
        }

        foreach ($type->getTypes() as $unionedType) {
            if (! $this->isAlwaysTruableType($unionedType)) {
                return false;
            }
        }

        return true;
    }
}
