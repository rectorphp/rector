<?php

declare(strict_types=1);

namespace Rector\PostRector\DependencyInjection;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
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
        private RectorChangeCollector $rectorChangeCollector,
        private PropertyNaming $propertyNaming
    ) {
    }

    public function addPropertyToCollector(Property $property): void
    {
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return;
        }

        $propertyType = $this->nodeTypeResolver->resolve($property);

        // use first type - hard assumption @todo improve
        if ($propertyType instanceof UnionType) {
            $propertyType = $propertyType->getTypes()[0];
        }

        $propertyName = $this->nodeNameResolver->getName($property);
        $this->addConstructorDependencyToClass($classNode, $propertyType, $propertyName, $property->flags);
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

        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }

    public function addServiceConstructorDependencyToClass(Class_ $class, ObjectType $objectType): void
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $this->addConstructorDependencyToClass($class, $objectType, $propertyName);
    }
}
