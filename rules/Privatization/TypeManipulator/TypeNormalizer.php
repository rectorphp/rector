<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Privatization\TypeManipulator;

use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantBooleanType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\TypeTraverser;
final class TypeNormalizer
{
    /**
     * Generalize false/true type to bool,
     * as mostly default value but accepts both
     */
    public function generalizeConstantBoolTypes(Type $type) : Type
    {
        return TypeTraverser::map($type, function (Type $type, callable $traverseCallback) {
            if ($type instanceof ConstantBooleanType) {
                return new BooleanType();
            }
            return $traverseCallback($type, $traverseCallback);
        });
    }
}
