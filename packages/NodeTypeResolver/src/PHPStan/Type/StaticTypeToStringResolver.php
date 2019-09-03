<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

/**
 * @see \Rector\NodeTypeResolver\Tests\StaticTypeToStringResolverTest
 */
final class StaticTypeToStringResolver
{
    /**
     * @return string[]
     */
    public function resolveAnyType(Type $type): array
    {
        $types = [];

        if ($type instanceof ObjectType) {
            $types[] = $type->getClassName();
        }

        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            foreach ($type->getTypes() as $subType) {
                if ($subType instanceof ObjectType) {
                    $types[] = $subType->getClassName();
                }
            }
        }

        if ($type instanceof ConstantType) {
            return $this->resolveConstantType($type);
        }

        return $types;
    }

    /**
     * @return string[]
     */
    private function resolveConstantArrayType(ConstantArrayType $constantArrayType): array
    {
        $arrayTypes = [];

        foreach ($constantArrayType->getValueTypes() as $valueType) {
            $arrayTypes = array_merge($arrayTypes, $this->resolveAnyType($valueType));
        }

        $arrayTypes = array_unique($arrayTypes);

        return array_map(function (string $arrayType): string {
            return $arrayType . '[]';
        }, $arrayTypes);
    }

    /**
     * @return string[]
     */
    private function resolveConstantType(ConstantType $constantType): array
    {
        if ($constantType instanceof ConstantBooleanType) {
            return ['bool'];
        }

        if ($constantType instanceof ConstantStringType) {
            return ['string'];
        }

        if ($constantType instanceof ConstantIntegerType) {
            return ['int'];
        }

        if ($constantType instanceof ConstantArrayType) {
            return $this->resolveConstantArrayType($constantType);
        }

        if ($constantType instanceof ConstantFloatType) {
            return ['float'];
        }

        return [];
    }
}
