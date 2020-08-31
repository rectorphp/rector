<?php

declare(strict_types=1);

namespace Rector\Decouple\UsedNodesExtractor;

use function array_keys;
use function array_merge;
use function in_array;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

final class UsedClassMethodsExtractor
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
     * @return ClassMethod[]
     */
    public function extractFromClassMethod(ClassMethod $classMethod, ?string $parentClassName = null): array
    {
        /** @var Class_ $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);

        $classMethods = [];

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            &$classMethods,
            $classLike
        ): ?void {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isThisPropertyFetch($node->var)) {
                return null;
            }

            /** @var string $methodName */
            $methodName = $this->nodeNameResolver->getName($node->name);

            $classMethod = $classLike->getMethod($methodName);
            if ($classMethod === null) {
                throw new ShouldNotHappenException();
            }

            $classMethods[$methodName] = $classMethod;
        });

        // 2nd nesting method calls
        foreach ($classMethods as $nestedClassMethod) {
            $classMethods = array_merge($classMethods, $this->extractFromClassMethod($nestedClassMethod));
        }

        $uniqueClassMethods = $this->makeClassesMethodsUnique($classMethods);

        if ($parentClassName !== null) {
            return $this->filterOutParentClassMethods($uniqueClassMethods, $parentClassName);
        }

        return $uniqueClassMethods;
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

    /**
     * @param ClassMethod[] $classMethods
     * @return ClassMethod[]
     */
    private function makeClassesMethodsUnique(array $classMethods): array
    {
        $uniqueClassMethods = [];

        foreach ($classMethods as $classMethod) {
            /** @var string $classMethodName */
            $classMethodName = $this->nodeNameResolver->getName($classMethod);
            $uniqueClassMethods[$classMethodName] = $classMethod;
        }

        return $uniqueClassMethods;
    }

    /**
     * @param ClassMethod[] $classMethods
     * @return ClassMethod[]
     */
    private function filterOutParentClassMethods(array $classMethods, string $parentClassName): array
    {
        $reflectionClass = new ReflectionClass($parentClassName);
        $parentClassMethodNames = [];
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $parentClassMethodNames[] = $reflectionMethod->getName();
        }

        foreach (array_keys($classMethods) as $methodName) {
            if (! in_array($methodName, $parentClassMethodNames, true)) {
                continue;
            }

            unset($classMethods[$methodName]);
        }

        return $classMethods;
    }
}
