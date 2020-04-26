<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer;

final class PropertyTypeInferer extends AbstractPriorityAwareTypeInferer
{
    /**
     * @var PropertyTypeInfererInterface[]
     */
    private $propertyTypeInferers = [];

    /**
     * @var DefaultValuePropertyTypeInferer
     */
    private $defaultValuePropertyTypeInferer;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;

    /**
     * @param PropertyTypeInfererInterface[] $propertyTypeInferers
     */
    public function __construct(
        array $propertyTypeInferers,
        DefaultValuePropertyTypeInferer $defaultValuePropertyTypeInferer,
        TypeFactory $typeFactory,
        DoctrineTypeAnalyzer $doctrineTypeAnalyzer
    ) {
        $this->propertyTypeInferers = $this->sortTypeInferersByPriority($propertyTypeInferers);
        $this->defaultValuePropertyTypeInferer = $defaultValuePropertyTypeInferer;
        $this->typeFactory = $typeFactory;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
    }

    public function inferProperty(Property $property): Type
    {
        foreach ($this->propertyTypeInferers as $propertyTypeInferer) {
            $type = $propertyTypeInferer->inferProperty($property);
            if ($type instanceof VoidType || $type instanceof MixedType) {
                continue;
            }

            // default value type must be added to each resolved type
            $defaultValueType = $this->defaultValuePropertyTypeInferer->inferProperty($property);
            if ($this->shouldUnionWithDefaultValue($defaultValueType, $type)) {
                return $this->unionWithDefaultValueType($type, $defaultValueType);
            }

            return $type;
        }

        return new MixedType();
    }

    private function shouldUnionWithDefaultValue(Type $defaultValueType, Type $type): bool
    {
        if ($defaultValueType instanceof MixedType) {
            return false;
        }

        return ! $this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type);
    }

    private function unionWithDefaultValueType(Type $type, Type $defaultValueType): Type
    {
        // default type has bigger priority than @var type, if not nullable type
        if (! $defaultValueType instanceof NullType) {
            return $defaultValueType;
        }

        $types = [$type, $defaultValueType];
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
