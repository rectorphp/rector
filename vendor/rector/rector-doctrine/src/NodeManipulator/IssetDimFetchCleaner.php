<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Throw_ as ThrowStmt;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
final class IssetDimFetchCleaner
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, ValueResolver $valueResolver, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->valueResolver = $valueResolver;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @return string[]
     */
    public function resolveOptionalParamNames(ClassMethod $classMethod, Variable $paramVariable) : array
    {
        $optionalParamNames = [];
        if ($classMethod->stmts === null) {
            return [];
        }
        foreach ($classMethod->stmts as $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            $if = $stmt;
            /** @var Isset_|null $isset */
            $isset = $this->betterNodeFinder->findFirstInstanceOf($if->cond, Isset_::class);
            if (!$isset instanceof Isset_) {
                continue;
            }
            foreach ($isset->vars as $var) {
                $dimFetchValue = $this->matchArrayDimFetchValue($var, $paramVariable);
                if (!\is_string($dimFetchValue)) {
                    continue;
                }
                // is required or optional?
                if ($this->isRequiredIsset($if)) {
                    // contains exception or required param → skip
                    continue;
                }
                // else optional param
                $optionalParamNames[] = $dimFetchValue;
            }
        }
        return $optionalParamNames;
    }
    public function removeArrayDimFetchIssets(ClassMethod $classMethod, Variable $paramVariable) : void
    {
        if ($classMethod->stmts === null) {
            return;
        }
        foreach ($classMethod->stmts as $key => $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            $if = $stmt;
            /** @var Isset_|null $isset */
            $isset = $this->betterNodeFinder->findFirstInstanceOf($if->cond, Isset_::class);
            if (!$isset instanceof Isset_) {
                continue;
            }
            foreach ($isset->vars as $var) {
                if (!$this->isArrayDimFetchOnVariable($var, $paramVariable)) {
                    continue;
                }
                // remove if stmt, this check is not part of __constuct() contract
                unset($classMethod->stmts[$key]);
            }
        }
    }
    private function isArrayDimFetchOnVariable(Expr $expr, Variable $desiredVariable) : bool
    {
        if (!$expr instanceof ArrayDimFetch) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($desiredVariable, $expr->var);
    }
    /**
     * @return mixed|mixed[]|string|null
     */
    private function matchArrayDimFetchValue(Expr $expr, Variable $variable)
    {
        if (!$expr instanceof ArrayDimFetch) {
            return null;
        }
        if (!$expr->dim instanceof Expr) {
            return null;
        }
        if (!$this->isArrayDimFetchOnVariable($expr, $variable)) {
            return null;
        }
        return $this->valueResolver->getValue($expr->dim);
    }
    private function isRequiredIsset(If_ $if) : bool
    {
        return $this->betterNodeFinder->hasInstancesOf($if->stmts, [Throw_::class, ThrowStmt::class]);
    }
}
