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
    public function isArrayUnionResponseType(Type $type, string $className) : bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        $hasArrayType = \false;
        $hasResponseType = \false;
        foreach ($type->getTypes() as $unionedType) {
            if ($unionedType instanceof ArrayType) {
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
    private function isTypeOfClassName(Type $type, string $className) : bool
    {
        if (!$type instanceof TypeWithClassName) {
            return \false;
        }
        $objectType = new ObjectType($className);
        return $objectType->isSuperTypeOf($type)->yes();
    }
}
