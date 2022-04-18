<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220418\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ClassMethodPropertyFetchManipulator
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
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\RectorPrefix20220418\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * In case the property name is different to param name:
     *
     * E.g.:
     * (SomeType $anotherValue)
     * $this->value = $anotherValue;
     * ↓
     * (SomeType $anotherValue)
     */
    public function findParamAssignToPropertyName(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : ?\PhpParser\Node\Param
    {
        $assignedParamName = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use($propertyName, &$assignedParamName, $classMethod) : ?int {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, $propertyName)) {
                return null;
            }
            if ($node->expr instanceof \PhpParser\Node\Expr\MethodCall || $node->expr instanceof \PhpParser\Node\Expr\StaticCall) {
                return null;
            }
            $parentClassMethod = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassMethod::class);
            if ($parentClassMethod !== $classMethod) {
                return null;
            }
            $assignedParamName = $this->nodeNameResolver->getName($node->expr);
            return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
        });
        /** @var string|null $assignedParamName */
        if ($assignedParamName === null) {
            return null;
        }
        /** @var Param $param */
        foreach ($classMethod->params as $param) {
            if (!$this->nodeNameResolver->isName($param, $assignedParamName)) {
                continue;
            }
            return $param;
        }
        return null;
    }
    /**
     * E.g.:
     * $this->value = 1000;
     * ↓
     * (int $value)
     *
     * @return Expr[]
     */
    public function findAssignsToPropertyName(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : array
    {
        $assignExprs = [];
        $paramNames = $this->getParamNames($classMethod);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use($propertyName, &$assignExprs, $paramNames, $classMethod) : ?int {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, $propertyName)) {
                return null;
            }
            // skip param assigns
            if ($this->nodeNameResolver->isNames($node->expr, $paramNames)) {
                return null;
            }
            $parentClassMethod = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassMethod::class);
            if ($parentClassMethod !== $classMethod) {
                return null;
            }
            $assignExprs[] = $node->expr;
            return null;
        });
        return $assignExprs;
    }
    /**
     * @return string[]
     */
    private function getParamNames(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $paramNames = [];
        foreach ($classMethod->getParams() as $param) {
            $paramNames[] = $this->nodeNameResolver->getName($param);
        }
        return $paramNames;
    }
}
