<?php

declare (strict_types=1);
namespace Rector\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitor;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class ClassMethodPropertyFetchManipulator
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private \Rector\NodeManipulator\FunctionLikeManipulator $functionLikeManipulator;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, \Rector\NodeManipulator\FunctionLikeManipulator $functionLikeManipulator)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
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
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use($propertyName, &$assignedParamName) : ?int {
            if ($node instanceof Class_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
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
            $assignedParamName = $this->nodeNameResolver->getName($node->expr);
            return NodeVisitor::STOP_TRAVERSAL;
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
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use($propertyName, &$assignExprs, $paramNames) : ?int {
            if ($node instanceof Class_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
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
            $assignExprs[] = $node->expr;
            return null;
        });
        return $assignExprs;
    }
}
