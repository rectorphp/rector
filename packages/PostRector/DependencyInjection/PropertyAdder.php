<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector, \Rector\Naming\Naming\PropertyNaming $propertyNaming)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->propertyNaming = $propertyNaming;
    }
    public function addPropertyToCollector(\PhpParser\Node\Stmt\Property $property) : void
    {
        $class = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return;
        }
        $propertyType = $this->nodeTypeResolver->resolve($property);
        // use first type - hard assumption @todo improve
        if ($propertyType instanceof \PHPStan\Type\UnionType) {
            $propertyType = $propertyType->getTypes()[0];
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $propertyType, $property->flags);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
    }
    /**
     * @deprecated
     * Use directly @see PropertyToAddCollector::addPropertyToClass() directly with PropertyMetadata object
     */
    public function addConstructorDependencyToClass(\PhpParser\Node\Stmt\Class_ $class, \PHPStan\Type\Type $propertyType, string $propertyName, int $propertyFlags = 0) : void
    {
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $propertyType, $propertyFlags);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
    }
    public function addServiceConstructorDependencyToClass(\PhpParser\Node\Stmt\Class_ $class, \PHPStan\Type\ObjectType $objectType) : void
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $objectType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
    }
}
