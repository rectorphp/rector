<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\InvokableAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\ValueObject\InvokableController\ActiveClassElements;
use RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ActiveClassElementsClassMethodResolver
{
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolve(\PhpParser\Node\Stmt\ClassMethod $actionClassMethod) : \Rector\Symfony\ValueObject\InvokableController\ActiveClassElements
    {
        $usedLocalPropertyNames = $this->resolveLocalUsedPropertyNames($actionClassMethod);
        $usedLocalConstantNames = $this->resolveLocalUsedConstantNames($actionClassMethod);
        $usedLocalMethodNames = $this->resolveLocalUsedMethodNames($actionClassMethod);
        return new \Rector\Symfony\ValueObject\InvokableController\ActiveClassElements($usedLocalPropertyNames, $usedLocalConstantNames, $usedLocalMethodNames);
    }
    /**
     * @return string[]
     */
    private function resolveLocalUsedPropertyNames(\PhpParser\Node\Stmt\ClassMethod $actionClassMethod) : array
    {
        $usedLocalPropertyNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($actionClassMethod, function (\PhpParser\Node $node) use(&$usedLocalPropertyNames) {
            if (!$node instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, 'this')) {
                return null;
            }
            $propertyName = $this->nodeNameResolver->getName($node->name);
            if (!\is_string($propertyName)) {
                return null;
            }
            $usedLocalPropertyNames[] = $propertyName;
        });
        return $usedLocalPropertyNames;
    }
    /**
     * @return string[]
     */
    private function resolveLocalUsedConstantNames(\PhpParser\Node\Stmt\ClassMethod $actionClassMethod) : array
    {
        $usedLocalConstantNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($actionClassMethod, function (\PhpParser\Node $node) use(&$usedLocalConstantNames) {
            if (!$node instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->class, 'self')) {
                return null;
            }
            $constantName = $this->nodeNameResolver->getName($node->name);
            if (!\is_string($constantName)) {
                return null;
            }
            $usedLocalConstantNames[] = $constantName;
        });
        return $usedLocalConstantNames;
    }
    /**
     * @return string[]
     */
    private function resolveLocalUsedMethodNames(\PhpParser\Node\Stmt\ClassMethod $actionClassMethod) : array
    {
        $usedLocalMethodNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($actionClassMethod, function (\PhpParser\Node $node) use(&$usedLocalMethodNames) {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, 'this')) {
                return null;
            }
            $methodName = $this->nodeNameResolver->getName($node->name);
            if (!\is_string($methodName)) {
                return null;
            }
            $usedLocalMethodNames[] = $methodName;
        });
        return $usedLocalMethodNames;
    }
}
