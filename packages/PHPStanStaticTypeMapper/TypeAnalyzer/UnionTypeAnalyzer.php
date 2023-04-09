<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IterableType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\ValueObject\UnionTypeAnalysis;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Traversable;
final class UnionTypeAnalyzer
{
    public function analyseForNullableAndIterable(UnionType $unionType) : ?UnionTypeAnalysis
    {
        $isNullableType = \false;
        $hasIterable = \false;
        $hasArray = \false;
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof IterableType) {
                $hasIterable = \true;
                continue;
            }
            if ($unionedType instanceof ArrayType) {
                $hasArray = \true;
                continue;
            }
            if ($unionedType instanceof NullType) {
                $isNullableType = \true;
                continue;
            }
            if ($unionedType instanceof ObjectType && $unionedType->getClassName() === Traversable::class) {
                $hasIterable = \true;
                continue;
            }
            return null;
        }
        return new UnionTypeAnalysis($isNullableType, $hasIterable, $hasArray);
    }
    /**
     * @return TypeWithClassName[]
     */
    public function matchExclusiveTypesWithClassNames(UnionType $unionType) : array
    {
        $typesWithClassNames = [];
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof TypeWithClassName) {
                return [];
            }
            $typesWithClassNames[] = $unionedType;
        }
        return $typesWithClassNames;
    }
    public function hasObjectWithoutClassType(UnionType $unionType) : bool
    {
        $types = $unionType->getTypes();
        foreach ($types as $type) {
            if ($type instanceof ObjectWithoutClassType) {
                return \true;
            }
        }
        return \false;
    }
    public function hasObjectWithoutClassTypeWithOnlyFullyQualifiedObjectType(UnionType $unionType) : bool
    {
        $types = $unionType->getTypes();
        foreach ($types as $type) {
            if ($type instanceof ObjectWithoutClassType) {
                continue;
            }
            if (!$type instanceof FullyQualifiedObjectType) {
                return \false;
            }
        }
        return \true;
    }
    public function isScalar(UnionType $unionType) : bool
    {
        $types = $unionType->getTypes();
        if (\count($types) !== 4) {
            return \false;
        }
        foreach ($types as $type) {
            if ($type->isString()->yes() && !$type instanceof ConstantStringType) {
                continue;
            }
            if ($type->isFloat()->yes()) {
                continue;
            }
            if ($type->isInteger()->yes()) {
                continue;
            }
            if ($type->isBoolean()->yes()) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    public function isNullable(UnionType $unionType, bool $checkTwoTypes = \false) : bool
    {
        $types = $unionType->getTypes();
        if ($checkTwoTypes && \count($types) > 2) {
            return \false;
        }
        foreach ($types as $type) {
            if ($type instanceof NullType) {
                return \true;
            }
        }
        return \false;
    }
}
