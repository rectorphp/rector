<?php

declare (strict_types=1);
namespace Rector\Privatization\TypeManipulator;

use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
final class TypeNormalizer
{
    /**
     * Generalize false/true type to bool,
     * as mostly default value but accepts both
     */
    public function generalizeConstantBoolTypes(Type $type) : Type
    {
        return TypeTraverser::map($type, static function (Type $type, callable $traverseCallback) {
            if ($type instanceof ConstantBooleanType) {
                return new BooleanType();
            }
            if ($type instanceof ConstantStringType) {
                return new StringType();
            }
            if ($type instanceof ConstantFloatType) {
                return new FloatType();
            }
            if ($type instanceof ConstantIntegerType) {
                return new IntegerType();
            }
            return $traverseCallback($type, $traverseCallback);
        });
    }
}
