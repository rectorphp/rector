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

final class PropertyAdder
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PropertyToAddCollector
     */
    private $propertyToAddCollector;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        NodeNameResolver $nodeNameResolver,
        PropertyToAddCollector $propertyToAddCollector,
        RectorChangeCollector $rectorChangeCollector,
        PropertyNaming $propertyNaming
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->propertyNaming = $propertyNaming;
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

    public function addConstructorDependencyToClass(
        Class_ $class,
        ?Type $propertyType,
        string $propertyName,
        int $propertyFlags = 0
    ): void {
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyName, $propertyType, $propertyFlags);
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }

    public function addServiceConstructorDependencyToClass(Class_ $class, string $className): void
    {
        $serviceObjectType = new ObjectType($className);

        $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);
        $this->addConstructorDependencyToClass($class, $serviceObjectType, $propertyName);
    }
}
