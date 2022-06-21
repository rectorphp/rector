<?php

declare (strict_types=1);
namespace Rector\Privatization\TypeManipulator;

use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
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
            return $traverseCallback($type, $traverseCallback);
        });
    }
}
