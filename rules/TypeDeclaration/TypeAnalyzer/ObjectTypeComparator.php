<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Type\CallableType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
final class ObjectTypeComparator
{
    /**
     * E.g. current E, new type A, E extends A → true
     * Also for closure/callable, iterable/Traversable/Iterator/Generator
     */
    public function isCurrentObjectTypeSubType(\PHPStan\Type\Type $currentType, \PHPStan\Type\Type $newType) : bool
    {
        if ($newType instanceof \PHPStan\Type\ObjectWithoutClassType && $currentType instanceof \PHPStan\Type\ObjectType) {
            return \true;
        }
        if ($this->isBothCallable($currentType, $newType)) {
            return \true;
        }
        if (!$currentType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        if (!$newType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        return $newType->isSuperTypeOf($currentType)->yes();
    }
    private function isBothCallable(\PHPStan\Type\Type $currentType, \PHPStan\Type\Type $newType) : bool
    {
        if ($currentType instanceof \PHPStan\Type\CallableType && $this->isClosure($newType)) {
            return \true;
        }
        return $newType instanceof \PHPStan\Type\CallableType && $this->isClosure($currentType);
    }
    private function isClosure(\PHPStan\Type\Type $type) : bool
    {
        $closureObjectType = new \PHPStan\Type\ObjectType('Closure');
        return $closureObjectType->equals($type);
    }
}
