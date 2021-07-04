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
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\Sorter\TypeInfererSorter;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\VarDocPropertyTypeInferer;

final class PropertyTypeInferer
{
    /**
     * @var PropertyTypeInfererInterface[]
     */
    private array $propertyTypeInferers = [];

    /**
     * @param PropertyTypeInfererInterface[] $propertyTypeInferers
     */
    public function __construct(
        TypeInfererSorter $typeInfererSorter,
        private GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer,
        private DefaultValuePropertyTypeInferer $defaultValuePropertyTypeInferer,
        private VarDocPropertyTypeInferer $varDocPropertyTypeInferer,
        private TypeFactory $typeFactory,
        private DoctrineTypeAnalyzer $doctrineTypeAnalyzer,
        array $propertyTypeInferers
    ) {
        $this->propertyTypeInferers = $typeInfererSorter->sort($propertyTypeInferers);
    }

    public function inferProperty(Property $property): Type
    {
        $resolvedTypes = [];

        foreach ($this->propertyTypeInferers as $propertyTypeInferer) {
            $type = $propertyTypeInferer->inferProperty($property);
            if (! $type instanceof Type) {
                continue;
            }

            if ($type instanceof VoidType) {
                continue;
            }

            $resolvedTypes[] = $type;
        }

        $resolvedTypes = $this->typeFactory->uniquateTypes($resolvedTypes);

        // if nothing is clear from variable use, we use @var doc as fallback
        if ($resolvedTypes !== []) {
            $resolvedType = $this->typeFactory->createMixedPassedOrUnionType($resolvedTypes);
        } else {
            // void type is not allowed in properties
            $resolvedType = $this->varDocPropertyTypeInferer->inferProperty($property);
            if ($resolvedType instanceof VoidType) {
                return new MixedType();
            }
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
