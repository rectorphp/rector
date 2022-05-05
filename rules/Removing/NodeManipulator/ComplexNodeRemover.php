<?php

declare (strict_types=1);
namespace Rector\Removing\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use RectorPrefix20220505\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
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
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeRemoval\NodeRemover $nodeRemover, \Rector\DeadCode\SideEffect\SideEffectNodeDetector $sideEffectNodeDetector, \RectorPrefix20220505\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeRemover = $nodeRemover;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function removePropertyAndUsages(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\Property $property, bool $removeAssignSideEffect) : void
    {
        $propertyName = $this->nodeNameResolver->getName($property);
        $hasSideEffect = \false;
        $isPartoFAnotherAssign = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) use($removeAssignSideEffect, $propertyName, &$hasSideEffect, &$isPartoFAnotherAssign) {
            // here should be checked all expr like stmts that can hold assign, e.f. if, foreach etc. etc.
            if ($node instanceof \PhpParser\Node\Stmt\Expression) {
                $nodeExpr = $node->expr;
                // remove direct assigns
                if ($nodeExpr instanceof \PhpParser\Node\Expr\Assign) {
                    $assign = $nodeExpr;
                    // skip double assigns
                    if ($assign->expr instanceof \PhpParser\Node\Expr\Assign) {
                        $isPartoFAnotherAssign = \true;
                        return null;
                    }
                    $propertyFetches = $this->resolvePropertyFetchFromDimFetch($assign->var);
                    if ($propertyFetches === []) {
                        return null;
                    }
                    foreach ($propertyFetches as $propertyFetch) {
                        if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                            continue;
                        }
                        if ($this->nodeNameResolver->isName($propertyFetch->name, $propertyName)) {
                            if (!$removeAssignSideEffect && $this->sideEffectNodeDetector->detect($assign->expr)) {
                                $hasSideEffect = \true;
                                return null;
                            }
                            $this->nodeRemover->removeNode($node);
                        }
                    }
                }
            }
            return null;
        });
        // do not remove anyhting in case of side-effect
        if ($hasSideEffect) {
            return;
        }
        if ($isPartoFAnotherAssign) {
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
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            $stmtExpr = $stmt->expr;
            if ($stmtExpr instanceof \PhpParser\Node\Expr\Assign && $stmtExpr->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                $propertyFetch = $stmtExpr->var;
                if ($this->nodeNameResolver->isName($propertyFetch, $propertyName)) {
                    unset($classMethod->stmts[$key]);
                    if ($stmtExpr->expr instanceof \PhpParser\Node\Expr\Variable) {
                        $this->clearParamFromConstructor($classMethod, $stmtExpr->expr);
                    }
                }
            }
        }
    }
    /**
     * @return PropertyFetch[]
     */
    private function resolvePropertyFetchFromDimFetch(\PhpParser\Node\Expr $expr) : array
    {
        // unwrap array dim fetch, till we get to parent too caller node
        $propertyFetches = [];
        while ($expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            if ($expr->dim instanceof \PhpParser\Node\Expr\PropertyFetch) {
                $propertyFetches[] = $expr->dim;
            }
            $expr = $expr->var;
        }
        if ($expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $propertyFetches[] = $expr;
        }
        return $propertyFetches;
    }
    private function clearParamFromConstructor(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\Variable $assignedVariable) : void
    {
        // is variable used somewhere else? skip it
        $variables = $this->betterNodeFinder->findInstanceOf($classMethod, \PhpParser\Node\Expr\Variable::class);
        $paramNamedVariables = \array_filter($variables, function (\PhpParser\Node\Expr\Variable $variable) use($assignedVariable) : bool {
            return $this->nodeNameResolver->areNamesEqual($variable, $assignedVariable);
        });
        // there is more than 1 use, keep it in the constructor
        if (\count($paramNamedVariables) > 1) {
            return;
        }
        $paramName = $this->nodeNameResolver->getName($assignedVariable);
        if (!\is_string($paramName)) {
            return;
        }
        foreach ($classMethod->params as $paramKey => $param) {
            if ($this->nodeNameResolver->isName($param->var, $paramName)) {
                unset($classMethod->params[$paramKey]);
            }
        }
    }
}
