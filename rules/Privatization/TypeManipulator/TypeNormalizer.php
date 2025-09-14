<?php

declare (strict_types=1);
namespace Rector\Privatization\TypeManipulator;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;
final class TypeNormalizer
{
    /**
     * @deprecated This method is deprecated and will be removed in the next major release.
     * Use @see generalizeConstantTypes() instead.
     */
    public function generalizeConstantBoolTypes(\PHPStan\Type\Type $type): Type
    {
        return $this->generalizeConstantTypes($type);
    }
    /**
     * Generalize false/true constantArrayType to bool,
     * as mostly default value but accepts both
     */
    public function generalizeConstantTypes(Type $type): Type
    {
        return TypeTraverser::map($type, function (Type $type, callable $traverseCallback): Type {
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
            if ($type instanceof ConstantArrayType) {
                // is relevant int constantArrayType?
                if ($this->isImplicitNumberedListKeyType($type)) {
                    $keyType = new MixedType();
                } else {
                    $keyType = $traverseCallback($type->getKeyType(), $traverseCallback);
                }
                // should be string[]
                $itemType = $traverseCallback($type->getItemType(), $traverseCallback);
                if ($itemType instanceof ConstantStringType) {
                    $itemType = new StringType();
                }
                return new ArrayType($keyType, $itemType);
            }
            return $traverseCallback($type, $traverseCallback);
        });
    }
    private function isImplicitNumberedListKeyType(ConstantArrayType $constantArrayType): bool
    {
        if (!$constantArrayType->getKeyType() instanceof UnionType) {
            return \false;
        }
        foreach ($constantArrayType->getKeyType()->getTypes() as $key => $keyType) {
            if ($keyType instanceof ConstantIntegerType) {
                if ($keyType->getValue() === $key) {
                    continue;
                }
                return \false;
            }
            return \false;
        }
        return \true;
    }
}
