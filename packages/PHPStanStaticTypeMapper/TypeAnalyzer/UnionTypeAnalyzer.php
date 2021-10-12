<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\ValueObject\UnionTypeAnalysis;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Traversable;
final class UnionTypeAnalyzer
{
    public function analyseForNullableAndIterable(\PHPStan\Type\UnionType $unionType) : ?\Rector\PHPStanStaticTypeMapper\ValueObject\UnionTypeAnalysis
    {
        $isNullableType = \false;
        $hasIterable = \false;
        $hasArray = \false;
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof \PHPStan\Type\IterableType) {
                $hasIterable = \true;
                continue;
            }
            if ($unionedType instanceof \PHPStan\Type\ArrayType) {
                $hasArray = \true;
                continue;
            }
            if ($unionedType instanceof \PHPStan\Type\NullType) {
                $isNullableType = \true;
                continue;
            }
            if ($unionedType instanceof \PHPStan\Type\ObjectType && $unionedType->getClassName() === \Traversable::class) {
                $hasIterable = \true;
                continue;
            }
            return null;
        }
        return new \Rector\PHPStanStaticTypeMapper\ValueObject\UnionTypeAnalysis($isNullableType, $hasIterable, $hasArray);
    }
    public function hasTypeClassNameOnly(\PHPStan\Type\UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof \PHPStan\Type\TypeWithClassName) {
                return \false;
            }
        }
        return \true;
    }
    public function hasObjectWithoutClassType(\PHPStan\Type\UnionType $unionType) : bool
    {
        $types = $unionType->getTypes();
        foreach ($types as $type) {
            if ($type instanceof \PHPStan\Type\ObjectWithoutClassType) {
                return \true;
            }
        }
        return \false;
    }
    public function hasObjectWithoutClassTypeWithOnlyFullyQualifiedObjectType(\PHPStan\Type\UnionType $unionType) : bool
    {
        $types = $unionType->getTypes();
        foreach ($types as $type) {
            if ($type instanceof \PHPStan\Type\ObjectWithoutClassType) {
                continue;
            }
            if (!$type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
                return \false;
            }
        }
        return \true;
    }
    public function isScalar(\PHPStan\Type\UnionType $unionType) : bool
    {
        $types = $unionType->getTypes();
        if (\count($types) !== 4) {
            return \false;
        }
        foreach ($types as $type) {
            if ($type instanceof \PHPStan\Type\StringType && !$type instanceof \PHPStan\Type\Constant\ConstantStringType) {
                continue;
            }
            if ($type instanceof \PHPStan\Type\FloatType) {
                continue;
            }
            if ($type instanceof \PHPStan\Type\IntegerType) {
                continue;
            }
            if ($type instanceof \PHPStan\Type\BooleanType) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    public function isNullable(\PHPStan\Type\UnionType $unionType, bool $checkTwoTypes = \false) : bool
    {
        $types = $unionType->getTypes();
        if ($checkTwoTypes && \count($types) > 2) {
            return \false;
        }
        foreach ($types as $type) {
            if ($type instanceof \PHPStan\Type\NullType) {
                return \true;
            }
        }
        return \false;
    }
}
