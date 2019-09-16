<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
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
     * @param PropertyTypeInfererInterface[] $propertyTypeInferers
     */
    public function __construct(
        array $propertyTypeInferers,
        DefaultValuePropertyTypeInferer $defaultValuePropertyTypeInferer,
        TypeFactory $typeFactory
    ) {
        $this->propertyTypeInferers = $this->sortTypeInferersByPriority($propertyTypeInferers);
        $this->defaultValuePropertyTypeInferer = $defaultValuePropertyTypeInferer;
        $this->typeFactory = $typeFactory;
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
            if (! $defaultValueType instanceof MixedType) {
                return $this->unionWithDefaultValueType($type, $defaultValueType);
            }

            return $type;
        }

        return new MixedType();
    }

    private function unionWithDefaultValueType(Type $type, Type $defaultValueType): Type
    {
        // default type has bigger priority than @var type, if not nullable type
        if (! $defaultValueType instanceof NullType) {
            return $defaultValueType;
        }

        $types = array_merge([$type], [$defaultValueType]);
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
