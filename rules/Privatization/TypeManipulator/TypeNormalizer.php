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
    public function generalizeConstantBoolTypes(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $type, callable $traverseCallback) {
            if ($type instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
                return new \PHPStan\Type\BooleanType();
            }
            return $traverseCallback($type, $traverseCallback);
        });
    }
}
