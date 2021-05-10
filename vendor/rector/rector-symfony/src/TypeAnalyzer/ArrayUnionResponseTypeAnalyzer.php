<?php

declare (strict_types=1);
namespace Rector\Symfony\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
final class ArrayUnionResponseTypeAnalyzer
{
    /**
     * @param class-string $className
     */
    public function isArrayUnionResponseType(\PHPStan\Type\Type $type, string $className) : bool
    {
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return \false;
        }
        $hasArrayType = \false;
        $hasResponseType = \false;
        foreach ($type->getTypes() as $unionedType) {
            if ($unionedType instanceof \PHPStan\Type\ArrayType) {
                $hasArrayType = \true;
                continue;
            }
            if ($this->isTypeOfClassName($unionedType, $className)) {
                $hasResponseType = \true;
                continue;
            }
            return \false;
        }
        if (!$hasArrayType) {
            return \false;
        }
        return $hasResponseType;
    }
    /**
     * @param class-string $className
     */
    private function isTypeOfClassName(\PHPStan\Type\Type $type, string $className) : bool
    {
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        $objectType = new \PHPStan\Type\ObjectType($className);
        return $objectType->isSuperTypeOf($type)->yes();
    }
}
