<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class TypeFinder
{
    /**
     * @template TType as Type
     * @param class-string<TType> $desiredTypeClass
     */
    public function find(Type $type, string $desiredTypeClass) : Type
    {
        if ($type instanceof $desiredTypeClass) {
            return $type;
        }
        if ($type instanceof ArrayType && $type->getItemType() instanceof $desiredTypeClass) {
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
     * @param class-string<Type> $desiredTypeClass
     * @param \PHPStan\Type\UnionType|\PHPStan\Type\IntersectionType $compoundType
     */
    private function findInJoinedType($compoundType, string $desiredTypeClass) : Type
    {
        foreach ($compoundType->getTypes() as $joinedType) {
            $foundType = $this->find($joinedType, $desiredTypeClass);
            if (!$foundType instanceof MixedType) {
                return $foundType;
            }
        }
        return new MixedType();
    }
}
