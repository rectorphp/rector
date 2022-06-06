<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\TypeAnalyzer;

use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\IntersectionType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
final class TypeFinder
{
    /**
     * @template TType as Type
     * @param class-string<TType> $desiredTypeClass
     */
    public function find(Type $type, string $desiredTypeClass) : Type
    {
        if (\is_a($type, $desiredTypeClass, \true)) {
            return $type;
        }
        if ($type instanceof ArrayType && \is_a($type->getItemType(), $desiredTypeClass, \true)) {
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
