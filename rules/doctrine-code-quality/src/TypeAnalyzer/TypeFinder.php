<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class TypeFinder
{
    public function find(Type $type, string $desiredTypeClass): Type
    {
        if (is_a($type, $desiredTypeClass, true)) {
            return $type;
        }

        if ($type instanceof ArrayType && is_a($type->getItemType(), $desiredTypeClass, true)) {
            return $type->getItemType();
        }
        if ($type instanceof UnionType) {
            return $this->findInJoinedType($type, $desiredTypeClass);
        }
        if ($type instanceof IntersectionType) {
            return $this->findInJoinedType($type, $desiredTypeClass);
        }

        return new MixedType();
    }

    /**
     * @param UnionType|IntersectionType $type
     */
    private function findInJoinedType(Type $type, string $desiredTypeClass): Type
    {
        foreach ($type->getTypes() as $joinedType) {
            $foundType = $this->find($joinedType, $desiredTypeClass);
            if (! $foundType instanceof MixedType) {
                return $foundType;
            }
        }

        return new MixedType();
    }
}
