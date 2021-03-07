<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Type\CallableType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class ObjectTypeComparator
{
    /**
     * E.g. current E, new type A, E extends A → true
     * Also for closure/callable, iterable/Traversable/Iterator/Generator
     */
    public function isCurrentObjectTypeSubType(Type $currentType, Type $newType): bool
    {
        if ($this->isBothCallable($currentType, $newType)) {
            return true;
        }

        if ($this->isBothIterableIteratorGeneratorTraversable($currentType, $newType)) {
            return true;
        }

        if (! $currentType instanceof ObjectType) {
            return false;
        }

        if (! $newType instanceof ObjectType) {
            return false;
        }

        return is_a($currentType->getClassName(), $newType->getClassName(), true);
    }

    private function isClosure(Type $type): bool
    {
        return $type instanceof ObjectType && $type->getClassName() === 'Closure';
    }

    private function isBothCallable(Type $currentType, Type $newType): bool
    {
        if ($currentType instanceof CallableType && $this->isClosure($newType)) {
            return true;
        }

        return $newType instanceof CallableType && $this->isClosure($currentType);
    }

    private function isBothIterableIteratorGeneratorTraversable(Type $currentType, Type $newType): bool
    {
        if (! $currentType instanceof ObjectType) {
            return false;
        }

        if (! $newType instanceof ObjectType) {
            return false;
        }

        if ($currentType->getClassName() === 'iterable' && $this->isTraversableGeneratorIterator($newType)) {
            return true;
        }

        return $newType->getClassName() === 'iterable' && $this->isTraversableGeneratorIterator($currentType);
    }

    private function isTraversableGeneratorIterator(ObjectType $objectType): bool
    {
        return in_array($objectType->getClassName(), ['Traversable', 'Generator', 'Iterator'], true);
    }
}
