<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\IterableType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\ValueObject\UnionTypeAnalysis;
use Traversable;

final class UnionTypeAnalyzer
{
    public function analyseForNullableAndIterable(UnionType $unionType): ?UnionTypeAnalysis
    {
        $isNullableType = false;
        $hasIterable = false;
        $hasArray = false;

        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof IterableType) {
                $hasIterable = true;
                continue;
            }

            if ($unionedType instanceof ArrayType) {
                $hasArray = true;
                continue;
            }

            if ($unionedType instanceof NullType) {
                $isNullableType = true;
                continue;
            }

            if ($unionedType instanceof ObjectType && $unionedType->getClassName() === Traversable::class) {
                $hasIterable = true;
                continue;
            }

            return null;
        }

        return new UnionTypeAnalysis($isNullableType, $hasIterable, $hasArray);
    }

    public function hasTypeClassNameOnly(UnionType $unionType): bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                return false;
            }
        }

        return true;
    }
}
