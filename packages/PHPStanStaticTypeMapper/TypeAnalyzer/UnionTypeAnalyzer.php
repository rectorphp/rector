<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\ClassStringType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantStringType;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericClassStringType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\IterableType;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\ObjectWithoutClassType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\ValueObject\UnionTypeAnalysis;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
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
            if ($type instanceof StringType && !$type instanceof ConstantStringType) {
                continue;
            }
            if ($type instanceof FloatType) {
                continue;
            }
            if ($type instanceof IntegerType) {
                continue;
            }
            if ($type instanceof BooleanType) {
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
    public function mapGenericToClassStringType(UnionType $unionType) : UnionType
    {
        $types = $unionType->getTypes();
        foreach ($types as $key => $type) {
            if ($type instanceof GenericClassStringType) {
                $types[$key] = new ClassStringType();
            }
        }
        return new UnionType($types);
    }
}
