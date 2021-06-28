<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * This service contains all the parsed nodes. E.g. all the functions, method call, classes, static calls etc. It's
 * useful in case of context analysis, e.g. find all the usage of class method to detect, if the method is used.
 */
final class NodeRepository
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private ParsedNodeCollector $parsedNodeCollector,
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function hasClassChildren(Class_ $desiredClass): bool
    {
        $desiredClassName = $this->nodeNameResolver->getName($desiredClass);
        if ($desiredClassName === null) {
            return false;
        }

        return $this->findChildrenOfClass($desiredClassName) !== [];
    }

    /**
     * @deprecated Use ReflectionProvider instead to resolve all the traits
     * @return Trait_[]
     */
    public function findUsedTraitsInClass(ClassLike $classLike): array
    {
        $traits = [];

        foreach ($classLike->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitName = $this->nodeNameResolver->getName($trait);
                $foundTrait = $this->parsedNodeCollector->findTrait($traitName);
                if ($foundTrait !== null) {
                    $traits[] = $foundTrait;
                }
            }
        }

        return $traits;
    }

    /**
     * @return Class_[]|Interface_[]
     */
    public function findClassesAndInterfacesByType(string $type): array
    {
        return array_merge($this->findChildrenOfClass($type), $this->findImplementersOfInterface($type));
    }

    /**
     * @param class-string $class
     * @return Class_[]
     */
    public function findChildrenOfClass(string $class): array
    {
        $childrenClasses = [];

        foreach ($this->parsedNodeCollector->getClasses() as $classNode) {
            $currentClassName = $classNode->getAttribute(AttributeKey::CLASS_NAME);
            if (! $this->isChildOrEqualClassLike($class, $currentClassName)) {
                continue;
            }

            $childrenClasses[] = $classNode;
        }

        return $childrenClasses;
    }

    /**
     * @param class-string $class
     */
    public function findInterface(string $class): ?Interface_
    {
        return $this->parsedNodeCollector->findInterface($class);
    }

    /**
     * @param class-string $name
     */
    public function findClass(string $name): ?Class_
    {
        return $this->parsedNodeCollector->findClass($name);
    }

    public function findTrait(string $name): ?Trait_
    {
        return $this->parsedNodeCollector->findTrait($name);
    }

    public function findClassLike(string $classLikeName): ?ClassLike
    {
        return $this->findClass($classLikeName) ?? $this->findInterface($classLikeName) ?? $this->findTrait(
            $classLikeName
        );
    }

    private function isChildOrEqualClassLike(string $desiredClass, ?string $currentClassName): bool
    {
        if ($currentClassName === null) {
            return false;
        }

        if (! $this->reflectionProvider->hasClass($desiredClass)) {
            return false;
        }

        if (! $this->reflectionProvider->hasClass($currentClassName)) {
            return false;
        }

        $desiredClassReflection = $this->reflectionProvider->getClass($desiredClass);
        $currentClassReflection = $this->reflectionProvider->getClass($currentClassName);

        if (! $currentClassReflection->isSubclassOf($desiredClassReflection->getName())) {
            return false;
        }

        return $currentClassName !== $desiredClass;
    }

    /**
     * @return Interface_[]
     */
    private function findImplementersOfInterface(string $interface): array
    {
        $implementerInterfaces = [];

        foreach ($this->parsedNodeCollector->getInterfaces() as $interfaceNode) {
            $className = $interfaceNode->getAttribute(AttributeKey::CLASS_NAME);

            if (! $this->isChildOrEqualClassLike($interface, $className)) {
                continue;
            }

            $implementerInterfaces[] = $interfaceNode;
        }

        return $implementerInterfaces;
    }
}
