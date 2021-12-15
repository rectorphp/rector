<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\VarDocPropertyTypeInferer;

final class PropertyTypeInferer
{
    public function __construct(
        private readonly GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer,
        private readonly DefaultValuePropertyTypeInferer $defaultValuePropertyTypeInferer,
        private readonly VarDocPropertyTypeInferer $varDocPropertyTypeInferer,
        private readonly TypeFactory $typeFactory,
        private readonly DoctrineTypeAnalyzer $doctrineTypeAnalyzer,
    ) {
    }

    public function inferProperty(Property $property): Type
    {
        $resolvedType = $this->varDocPropertyTypeInferer->inferProperty($property);
        if ($resolvedType instanceof VoidType) {
            return new MixedType();
        }

        // default value type must be added to each resolved type if set
        $propertyDefaultValue = $property->props[0]->default;
        if ($propertyDefaultValue instanceof Expr) {
            $resolvedType = $this->unionTypeWithDefaultExpr($property, $resolvedType);
        }

        return $this->genericClassStringTypeNormalizer->normalize($resolvedType);
    }

    private function shouldUnionWithDefaultValue(Type $defaultValueType, Type $type): bool
    {
        if ($defaultValueType instanceof MixedType) {
            return false;
        }

        // skip empty array type (mixed[])
        if ($defaultValueType instanceof ArrayType && $defaultValueType->getItemType() instanceof NeverType && ! $type instanceof MixedType) {
            return false;
        }

        if ($type instanceof MixedType) {
            return true;
        }

        return ! $this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type);
    }

    private function unionWithDefaultValueType(Type $defaultValueType, Type $resolvedType): Type
    {
        $types = [];
        $types[] = $defaultValueType;

        if (! $resolvedType instanceof MixedType) {
            $types[] = $resolvedType;
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    private function unionTypeWithDefaultExpr(Property $property, Type $resolvedType): Type
    {
        $defaultValueType = $this->defaultValuePropertyTypeInferer->inferProperty($property);
        if (! $defaultValueType instanceof Type) {
            return $resolvedType;
        }

        if (! $this->shouldUnionWithDefaultValue($defaultValueType, $resolvedType)) {
            return $resolvedType;
        }

        return $this->unionWithDefaultValueType($defaultValueType, $resolvedType);
    }
}
