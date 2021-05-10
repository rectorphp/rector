<?php

declare (strict_types=1);
namespace Rector\Restoration\Type;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
final class ConstantReturnToParamTypeConverter
{
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }
    public function convert(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if ($type instanceof \PHPStan\Type\UnionType) {
            $flattenReturnTypes = \PHPStan\Type\TypeUtils::flattenTypes($type);
            $unionedTypes = [];
            foreach ($flattenReturnTypes as $flattenReturnType) {
                if ($flattenReturnType instanceof \PHPStan\Type\ArrayType) {
                    $unionedTypes[] = $flattenReturnType->getItemType();
                }
            }
            $resolvedTypes = [];
            foreach ($unionedTypes as $unionedType) {
                $resolvedTypes[] = $this->convert($unionedType);
            }
            return new \PHPStan\Type\UnionType($resolvedTypes);
        }
        if ($type instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return $this->unwrapConstantTypeToObjectType($type);
        }
        if ($type instanceof \PHPStan\Type\ArrayType) {
            return $this->unwrapConstantTypeToObjectType($type);
        }
        return new \PHPStan\Type\MixedType();
    }
    private function unwrapConstantTypeToObjectType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if ($type instanceof \PHPStan\Type\ArrayType) {
            return $this->unwrapConstantTypeToObjectType($type->getItemType());
        }
        if ($type instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return new \PHPStan\Type\ObjectType($type->getValue());
        }
        if ($type instanceof \PHPStan\Type\Generic\GenericClassStringType && $type->getGenericType() instanceof \PHPStan\Type\ObjectType) {
            return $type->getGenericType();
        }
        if ($type instanceof \PHPStan\Type\UnionType) {
            return $this->unwrapUnionType($type);
        }
        return new \PHPStan\Type\MixedType();
    }
    private function unwrapUnionType(\PHPStan\Type\UnionType $unionType) : \PHPStan\Type\Type
    {
        $types = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $unionType = $this->unwrapConstantTypeToObjectType($unionedType);
            if ($unionType !== null) {
                $types[] = $unionType;
            }
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
