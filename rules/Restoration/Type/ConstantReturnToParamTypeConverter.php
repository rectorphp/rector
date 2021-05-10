<?php

declare(strict_types=1);

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
    public function __construct(
        private TypeFactory $typeFactory
    ) {
    }

    public function convert(Type $type): Type
    {
        if ($type instanceof UnionType) {
            $flattenReturnTypes = TypeUtils::flattenTypes($type);
            $unionedTypes = [];
            foreach ($flattenReturnTypes as $flattenReturnType) {
                if ($flattenReturnType instanceof ArrayType) {
                    $unionedTypes[] = $flattenReturnType->getItemType();
                }
            }

            $resolvedTypes = [];
            foreach ($unionedTypes as $unionedType) {
                $resolvedTypes[] = $this->convert($unionedType);
            }

            return new UnionType($resolvedTypes);
        }

        if ($type instanceof ConstantStringType) {
            return $this->unwrapConstantTypeToObjectType($type);
        }

        if ($type instanceof ArrayType) {
            return $this->unwrapConstantTypeToObjectType($type);
        }

        return new MixedType();
    }

    private function unwrapConstantTypeToObjectType(Type $type): Type
    {
        if ($type instanceof ArrayType) {
            return $this->unwrapConstantTypeToObjectType($type->getItemType());
        }

        if ($type instanceof ConstantStringType) {
            return new ObjectType($type->getValue());
        }

        if ($type instanceof GenericClassStringType && $type->getGenericType() instanceof ObjectType) {
            return $type->getGenericType();
        }

        if ($type instanceof UnionType) {
            return $this->unwrapUnionType($type);
        }

        return new MixedType();
    }

    private function unwrapUnionType(UnionType $unionType): Type
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
