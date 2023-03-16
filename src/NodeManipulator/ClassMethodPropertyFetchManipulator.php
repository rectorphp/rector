<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class ClassMethodPropertyFetchManipulator
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
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
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\FunctionLikeManipulator
     */
    private $functionLikeManipulator;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, \Rector\Core\NodeManipulator\FunctionLikeManipulator $functionLikeManipulator)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->functionLikeManipulator = $functionLikeManipulator;
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
    public function findParamAssignToPropertyName(ClassMethod $classMethod, string $propertyName) : ?Param
    {
        $assignedParamName = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use($propertyName, &$assignedParamName, $classMethod) : ?int {
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$node->var instanceof PropertyFetch && !$node->var instanceof StaticPropertyFetch) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, $propertyName)) {
                return null;
            }
            if ($node->expr instanceof MethodCall || $node->expr instanceof StaticCall) {
                return null;
            }
            $parentClassMethod = $this->betterNodeFinder->findParentType($node, ClassMethod::class);
            if ($parentClassMethod !== $classMethod) {
                return null;
            }
            $assignedParamName = $this->nodeNameResolver->getName($node->expr);
            return NodeTraverser::STOP_TRAVERSAL;
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
    public function findAssignsToPropertyName(ClassMethod $classMethod, string $propertyName) : array
    {
        $assignExprs = [];
        $paramNames = $this->functionLikeManipulator->resolveParamNames($classMethod);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use($propertyName, &$assignExprs, $paramNames, $classMethod) : ?int {
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$node->var instanceof PropertyFetch && !$node->var instanceof StaticPropertyFetch) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, $propertyName)) {
                return null;
            }
            // skip param assigns
            if ($this->nodeNameResolver->isNames($node->expr, $paramNames)) {
                return null;
            }
            $parentClassMethod = $this->betterNodeFinder->findParentType($node, ClassMethod::class);
            if ($parentClassMethod !== $classMethod) {
                return null;
            }
            $assignExprs[] = $node->expr;
            return null;
        });
        return $assignExprs;
    }
}
