<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PostRector\Collector\NodesToRemoveCollector;

final class ClassManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        CallableNodeTraverser $callableNodeTraverser,
        NodesToRemoveCollector $nodesToRemoveCollector,
        NodeTypeResolver $nodeTypeResolver,
        PropertyFetchManipulator $propertyFetchManipulator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyFetchManipulator = $propertyFetchManipulator;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
    }

    /**
     * @param Class_|Trait_ $classLike
     * @return Name[]
     */
    public function getUsedTraits(ClassLike $classLike): array
    {
        $usedTraits = [];
        foreach ($classLike->getTraitUses() as $stmt) {
            foreach ($stmt->traits as $trait) {
                /** @var string $traitName */
                $traitName = $this->nodeNameResolver->getName($trait);
                $usedTraits[$traitName] = $trait;
            }
        }

        return $usedTraits;
    }

    public function hasParentMethodOrInterface(string $class, string $method): bool
    {
        if (! class_exists($class)) {
            return false;
        }

        $parentClass = $class;
        while ($parentClass = get_parent_class($parentClass)) {
            if (method_exists($parentClass, $method)) {
                return true;
            }
        }

        $implementedInterfaces = class_implements($class);
        foreach ($implementedInterfaces as $implementedInterface) {
            if (method_exists($implementedInterface, $method)) {
                return true;
            }
        }

        return false;
    }

    public function removeProperty(Class_ $class, string $propertyName): void
    {
        $this->removeProperties($class, [$propertyName]);
    }

    /**
     * @return string[]
     */
    public function getPrivatePropertyNames(Class_ $class): array
    {
        $privateProperties = array_filter($class->getProperties(), function (Property $property) {
            return $property->isPrivate();
        });

        return $this->nodeNameResolver->getNames($privateProperties);
    }

    /**
     * @return string[]
     */
    public function getPublicMethodNames(Class_ $class): array
    {
        $publicMethods = array_filter($class->getMethods(), function (ClassMethod $classMethod) {
            if ($classMethod->isAbstract()) {
                return false;
            }

            return $classMethod->isPublic();
        });

        return $this->nodeNameResolver->getNames($publicMethods);
    }

    public function findPropertyByType(Class_ $class, string $serviceType): ?Property
    {
        foreach ($class->getProperties() as $property) {
            if (! $this->nodeTypeResolver->isObjectType($property, $serviceType)) {
                continue;
            }

            return $property;
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getImplementedInterfaceNames(Class_ $class): array
    {
        return $this->nodeNameResolver->getNames($class->implements);
    }

    public function hasInterface(Class_ $class, string $desiredInterface): bool
    {
        return $this->nodeNameResolver->haveName($class->implements, $desiredInterface);
    }

    /**
     * @todo simplify
     * Waits on https://github.com/nikic/PHP-Parser/pull/646
     */
    public function hasPropertyName(Class_ $node, string $name): bool
    {
        foreach ($node->getProperties() as $property) {
            if ($this->nodeNameResolver->haveName($property->props, $name)) {
                return true;
            }
        }

        return false;
    }

    public function hasTrait(Class_ $class, string $desiredTrait): bool
    {
        foreach ($class->getTraitUses() as $traitUse) {
            if (! $this->nodeNameResolver->haveName($traitUse->traits, $desiredTrait)) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function replaceTrait(Class_ $class, string $oldTrait, string $newTrait): void
    {
        foreach ($class->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $key => $traitTrait) {
                if (! $this->nodeNameResolver->isName($traitTrait, $oldTrait)) {
                    continue;
                }

                $traitUse->traits[$key] = new FullyQualified($newTrait);
                break;
            }
        }
    }

    /**
     * @param Class_|Interface_ $classLike
     * @return string[]
     */
    public function getClassLikeNodeParentInterfaceNames(ClassLike $classLike)
    {
        if ($classLike instanceof Class_) {
            return $this->nodeNameResolver->getNames($classLike->implements);
        }

        if ($classLike instanceof Interface_) {
            return $this->nodeNameResolver->getNames($classLike->extends);
        }

        return [];
    }

    public function removeInterface(Class_ $class, string $desiredInterface): void
    {
        foreach ($class->implements as $implement) {
            if (! $this->nodeNameResolver->isName($implement, $desiredInterface)) {
                continue;
            }

            $this->nodesToRemoveCollector->addNodeToRemove($implement);
        }
    }

    /**
     * @param string[] $oldToNewPropertyNames
     */
    public function renamePropertyFetches(Class_ $class, array $oldToNewPropertyNames): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($class, function (Node $node) use (
            $oldToNewPropertyNames
        ) {
            if (! $this->propertyFetchManipulator->isLocalPropertyFetch($node)) {
                return null;
            }

            foreach ($oldToNewPropertyNames as $oldPropertyName => $newPropertyName) {
                if (! $this->nodeNameResolver->isName($node->name, $oldPropertyName)) {
                    continue;
                }

                $node->name = new Identifier($newPropertyName);
            }

            return null;
        });
    }

    /**
     * @param string[] $propertyNames
     */
    private function removeProperties(Class_ $class, array $propertyNames): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($class, function (Node $node) use ($propertyNames) {
            if (! $node instanceof Property) {
                return null;
            }

            if (! $this->nodeNameResolver->isNames($node, $propertyNames)) {
                return null;
            }

            $this->nodesToRemoveCollector->addNodeToRemove($node);
        });
    }
}
