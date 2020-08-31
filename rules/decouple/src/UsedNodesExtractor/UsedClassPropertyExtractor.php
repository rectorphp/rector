<?php

declare(strict_types=1);

namespace Rector\Decouple\UsedNodesExtractor;

use function array_keys;
use function array_merge;
use function in_array;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

final class UsedClassPropertyExtractor
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param ClassMethod[] $classMethods
     * @return \PhpParser\Node\Stmt\Property[]|array<int|string, \PhpParser\Node\Stmt\Property>
     */
    public function extractFromClassMethods(array $classMethods, ?string $parentClassName = null): array
    {
        $properties = [];

        foreach ($classMethods as $classMethod) {
            $properties = array_merge($properties, $this->extract($classMethod));
        }

        if ($parentClassName !== null) {
            return $this->filterOutParentClassProperties($properties, $parentClassName);
        }

        return $properties;
    }

    /**
     * @return Property[]
     */
    private function extract(ClassMethod $classMethod): array
    {
        $properties = [];

        /** @var Class_ $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            &$properties,
            $classLike
        ): ?void {
            if (! $node instanceof PropertyFetch) {
                return null;
            }

            if (! $this->isThisPropertyFetch($node->var)) {
                return null;
            }

            $propertyName = $this->nodeNameResolver->getName($node->name);
            if ($propertyName === null) {
                throw new ShouldNotHappenException();
            }

            /** @var Property|null $property */
            $property = $classLike->getProperty($propertyName);
            if ($property === null) {
                throw new ShouldNotHappenException();
            }

            $properties[$propertyName] = $property;
        });

        return $properties;
    }

    /**
     * @param Property[] $classProperties
     * @return Property[]
     */
    private function filterOutParentClassProperties(array $classProperties, string $parentClassName): array
    {
        $reflectionClass = new ReflectionClass($parentClassName);

        $parentClassPropertyNames = [];
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $parentClassPropertyNames[] = $reflectionProperty->getName();
        }

        foreach (array_keys($classProperties) as $propertyName) {
            if (! in_array($propertyName, $parentClassPropertyNames, true)) {
                continue;
            }

            unset($classProperties[$propertyName]);
        }

        return $classProperties;
    }

    private function isThisPropertyFetch(Node $node): bool
    {
        if ($node instanceof MethodCall) {
            return false;
        }

        if ($node instanceof StaticCall) {
            return false;
        }

        return $this->nodeNameResolver->isName($node, 'this');
    }
}
