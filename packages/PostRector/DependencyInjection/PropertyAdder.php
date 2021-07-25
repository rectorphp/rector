<?php

declare(strict_types=1);

namespace Rector\PostRector\DependencyInjection;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;

final class PropertyAdder
{
    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private NodeNameResolver $nodeNameResolver,
        private PropertyToAddCollector $propertyToAddCollector,
        private PropertyNaming $propertyNaming
    ) {
    }

    public function addPropertyToCollector(Property $property): void
    {
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return;
        }

        $propertyType = $this->nodeTypeResolver->resolve($property);

        // use first type - hard assumption @todo improve
        if ($propertyType instanceof UnionType) {
            $propertyType = $propertyType->getTypes()[0];
        }

        $propertyName = $this->nodeNameResolver->getName($property);

        $propertyMetadata = new PropertyMetadata($propertyName, $propertyType, $property->flags);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
    }

    /**
     * @deprecated
     * Use directly @see PropertyToAddCollector::addPropertyToClass() directly with PropertyMetadata object
     */
    public function addConstructorDependencyToClass(
        Class_ $class,
        Type $propertyType,
        string $propertyName,
        int $propertyFlags = 0
    ): void {
        $propertyMetadata = new PropertyMetadata($propertyName, $propertyType, $propertyFlags);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
    }

    public function addServiceConstructorDependencyToClass(Class_ $class, ObjectType $objectType): void
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);

        $propertyMetadata = new PropertyMetadata($propertyName, $objectType, Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
    }
}
