<?php

declare (strict_types=1);
namespace Rector\Removing\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ComplexNodeRemover
{
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
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeRemoval\NodeRemover $nodeRemover, \Rector\DeadCode\SideEffect\SideEffectNodeDetector $sideEffectNodeDetector, \RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeRemover = $nodeRemover;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function removePropertyAndUsages(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\Property $property, bool $removeAssignSideEffect) : void
    {
        $propertyName = $this->nodeNameResolver->getName($property);
        $hasSideEffect = \false;
        $isPartOfAnotherAssign = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) use($removeAssignSideEffect, $propertyName, &$hasSideEffect, &$isPartOfAnotherAssign) {
            // here should be checked all expr like stmts that can hold assign, e.f. if, foreach etc. etc.
            if (!$node instanceof \PhpParser\Node\Stmt\Expression) {
                return null;
            }
            $nodeExpr = $node->expr;
            // remove direct assigns
            if (!$nodeExpr instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            $assign = $nodeExpr;
            // skip double assigns
            if ($assign->expr instanceof \PhpParser\Node\Expr\Assign) {
                $isPartOfAnotherAssign = \true;
                return null;
            }
            $propertyFetches = $this->resolvePropertyFetchFromDimFetch($assign->var);
            if ($propertyFetches === []) {
                return null;
            }
            foreach ($propertyFetches as $propertyFetch) {
                if ($this->nodeNameResolver->isName($propertyFetch->name, $propertyName)) {
                    if (!$removeAssignSideEffect && $this->sideEffectNodeDetector->detect($assign->expr)) {
                        $hasSideEffect = \true;
                        return null;
                    }
                    $this->nodeRemover->removeNode($node);
                }
            }
            return null;
        });
        // do not remove anyhting in case of side-effect
        if ($hasSideEffect) {
            return;
        }
        if ($isPartOfAnotherAssign) {
            return;
        }
        $this->removeConstructorDependency($class, $propertyName);
        $this->nodeRemover->removeNode($property);
    }
    /**
     * @param Param[] $params
     * @param int[] $paramKeysToBeRemoved
     * @return int[]
     */
    public function processRemoveParamWithKeys(array $params, array $paramKeysToBeRemoved) : array
    {
        $totalKeys = \count($params) - 1;
        $removedParamKeys = [];
        foreach ($paramKeysToBeRemoved as $paramKeyToBeRemoved) {
            $startNextKey = $paramKeyToBeRemoved + 1;
            for ($nextKey = $startNextKey; $nextKey <= $totalKeys; ++$nextKey) {
                if (!isset($params[$nextKey])) {
                    // no next param, break the inner loop, remove the param
                    break;
                }
                if (\in_array($nextKey, $paramKeysToBeRemoved, \true)) {
                    // keep searching next key not in $paramKeysToBeRemoved
                    continue;
                }
                return [];
            }
            $this->nodeRemover->removeNode($params[$paramKeyToBeRemoved]);
            $removedParamKeys[] = $paramKeyToBeRemoved;
        }
        return $removedParamKeys;
    }
    private function removeConstructorDependency(\PhpParser\Node\Stmt\Class_ $class, string $propertyName) : void
    {
        $classMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        $stmts = (array) $classMethod->stmts;
        $paramKeysToBeRemoved = [];
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            $stmtExpr = $stmt->expr;
            if (!$stmtExpr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            if (!$this->propertyFetchAnalyzer->isLocalPropertyFetch($stmtExpr->var)) {
                continue;
            }
            /** @var StaticPropertyFetch|PropertyFetch $propertyFetch */
            $propertyFetch = $stmtExpr->var;
            if (!$this->nodeNameResolver->isName($propertyFetch, $propertyName)) {
                continue;
            }
            unset($classMethod->stmts[$key]);
            if (!$stmtExpr->expr instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            $key = $this->resolveToBeClearedParamFromConstructor($classMethod, $stmtExpr->expr);
            if (\is_int($key)) {
                $paramKeysToBeRemoved[] = $key;
            }
        }
        if ($paramKeysToBeRemoved === []) {
            return;
        }
        $this->processRemoveParamWithKeys($classMethod->getParams(), $paramKeysToBeRemoved);
    }
    /**
     * @return StaticPropertyFetch[]|PropertyFetch[]
     */
    private function resolvePropertyFetchFromDimFetch(\PhpParser\Node\Expr $expr) : array
    {
        // unwrap array dim fetch, till we get to parent too caller node
        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = [];
        while ($expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $propertyFetches = $this->collectPropertyFetches($expr->dim, $propertyFetches);
            $expr = $expr->var;
        }
        return $this->collectPropertyFetches($expr, $propertyFetches);
    }
    /**
     * @param StaticPropertyFetch[]|PropertyFetch[] $propertyFetches
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    private function collectPropertyFetches(?\PhpParser\Node\Expr $expr, array $propertyFetches) : array
    {
        if (!$expr instanceof \PhpParser\Node\Expr) {
            return $propertyFetches;
        }
        if ($this->propertyFetchAnalyzer->isLocalPropertyFetch($expr)) {
            /** @var StaticPropertyFetch|PropertyFetch $expr */
            return \array_merge($propertyFetches, [$expr]);
        }
        return $propertyFetches;
    }
    private function resolveToBeClearedParamFromConstructor(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\Variable $assignedVariable) : ?int
    {
        // is variable used somewhere else? skip it
        $variables = $this->betterNodeFinder->findInstanceOf($classMethod, \PhpParser\Node\Expr\Variable::class);
        $paramNamedVariables = \array_filter($variables, function (\PhpParser\Node\Expr\Variable $variable) use($assignedVariable) : bool {
            return $this->nodeNameResolver->areNamesEqual($variable, $assignedVariable);
        });
        // there is more than 1 use, keep it in the constructor
        if (\count($paramNamedVariables) > 1) {
            return null;
        }
        $paramName = $this->nodeNameResolver->getName($assignedVariable);
        if (!\is_string($paramName)) {
            return null;
        }
        foreach ($classMethod->params as $paramKey => $param) {
            if ($this->nodeNameResolver->isName($param->var, $paramName)) {
                return $paramKey;
            }
        }
        return null;
    }
}
