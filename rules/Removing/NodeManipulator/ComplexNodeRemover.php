<?php

declare (strict_types=1);
namespace Rector\Removing\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\AssignRemover;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToRemoveCollector;
final class ComplexNodeRemover
{
    /**
     * @var \Rector\NodeRemoval\AssignRemover
     */
    private $assignRemover;
    /**
     * @var \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder
     */
    private $propertyFetchFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    /**
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\NodeRemoval\AssignRemover $assignRemover, \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder $propertyFetchFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeRemoval\NodeRemover $nodeRemover, \Rector\PostRector\Collector\NodesToRemoveCollector $nodesToRemoveCollector, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->assignRemover = $assignRemover;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeRemover = $nodeRemover;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param string[] $classMethodNamesToSkip
     */
    public function removePropertyAndUsages(\PhpParser\Node\Stmt\Property $property, array $classMethodNamesToSkip = []) : void
    {
        $shouldKeepProperty = \false;
        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($property);
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->shouldSkipPropertyForClassMethod($propertyFetch, $classMethodNamesToSkip)) {
                $shouldKeepProperty = \true;
                continue;
            }
            $assign = $this->resolveAssign($propertyFetch);
            if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
                return;
            }
            // remove assigns
            $this->assignRemover->removeAssignNode($assign);
            $this->removeConstructorDependency($assign);
        }
        if ($shouldKeepProperty) {
            return;
        }
        // remove __construct param
        /** @var Property $property */
        $this->nodeRemover->removeNode($property);
        foreach ($property->props as $prop) {
            if (!$this->nodesToRemoveCollector->isNodeRemoved($prop)) {
                // if the property has at least one node left -> return
                return;
            }
        }
        $this->nodeRemover->removeNode($property);
    }
    /**
     * @param string[] $classMethodNamesToSkip
     * @param \PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr\PropertyFetch $expr
     */
    private function shouldSkipPropertyForClassMethod($expr, array $classMethodNamesToSkip) : bool
    {
        $classMethodNode = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethodNode instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        $classMethodName = $this->nodeNameResolver->getName($classMethodNode);
        return \in_array($classMethodName, $classMethodNamesToSkip, \true);
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $expr
     */
    private function resolveAssign($expr) : ?\PhpParser\Node\Expr\Assign
    {
        $assign = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        while ($assign !== null && !$assign instanceof \PhpParser\Node\Expr\Assign) {
            $assign = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        }
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        return $assign;
    }
    private function removeConstructorDependency(\PhpParser\Node\Expr\Assign $assign) : void
    {
        $classMethod = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        if (!$this->nodeNameResolver->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return;
        }
        $class = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return;
        }
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        foreach ($constructClassMethod->getParams() as $param) {
            $variable = $this->betterNodeFinder->findFirst((array) $constructClassMethod->stmts, function (\PhpParser\Node $node) use($param) : bool {
                return $this->nodeComparator->areNodesEqual($param->var, $node);
            });
            if (!$variable instanceof \PhpParser\Node) {
                continue;
            }
            if ($this->isExpressionVariableNotAssign($variable)) {
                continue;
            }
            if (!$this->nodeComparator->areNodesEqual($param->var, $assign->expr)) {
                continue;
            }
            $this->nodeRemover->removeNode($param);
        }
    }
    private function isExpressionVariableNotAssign(\PhpParser\Node $node) : bool
    {
        if ($node !== null) {
            $expressionVariable = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$expressionVariable instanceof \PhpParser\Node\Expr\Assign) {
                return \true;
            }
        }
        return \false;
    }
}
