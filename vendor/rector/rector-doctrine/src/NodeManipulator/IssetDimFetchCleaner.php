<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Throw_ as ThrowStmt;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class IssetDimFetchCleaner
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\NodeRemoval\NodeRemover $nodeRemover)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->valueResolver = $valueResolver;
        $this->nodeComparator = $nodeComparator;
        $this->nodeRemover = $nodeRemover;
    }
    /**
     * @return string[]
     */
    public function resolveOptionalParamNames(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\Variable $paramVariable) : array
    {
        $optionalParamNames = [];
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\If_) {
                continue;
            }
            /** @var If_ $if */
            $if = $stmt;
            /** @var Isset_|null $isset */
            $isset = $this->betterNodeFinder->findFirstInstanceOf($if->cond, \PhpParser\Node\Expr\Isset_::class);
            if (!$isset instanceof \PhpParser\Node\Expr\Isset_) {
                continue;
            }
            foreach ($isset->vars as $var) {
                $dimFetchValue = $this->matchArrayDimFetchValue($var, $paramVariable);
                if (!\is_string($dimFetchValue)) {
                    continue;
                }
                // is required or optional?
                if ($this->isRequiredIsset($isset, $if)) {
                    // contains exception? → required param → skip
                    continue;
                }
                // else optional param
                $optionalParamNames[] = $dimFetchValue;
            }
        }
        return $optionalParamNames;
    }
    public function removeArrayDimFetchIssets(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\Variable $paramVariable) : void
    {
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\If_) {
                continue;
            }
            /** @var If_ $if */
            $if = $stmt;
            /** @var Isset_|null $isset */
            $isset = $this->betterNodeFinder->findFirstInstanceOf($if->cond, \PhpParser\Node\Expr\Isset_::class);
            if (!$isset instanceof \PhpParser\Node\Expr\Isset_) {
                continue;
            }
            foreach ($isset->vars as $var) {
                if (!$this->isArrayDimFetchOnVariable($var, $paramVariable)) {
                    continue;
                }
                // remove if stmt, this check is not part of __constuct() contract
                $this->nodeRemover->removeNode($if);
            }
        }
    }
    private function isArrayDimFetchOnVariable(\PhpParser\Node\Expr $var, \PhpParser\Node\Expr\Variable $desiredVariable) : bool
    {
        if (!$var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($desiredVariable, $var->var);
    }
    /**
     * @return mixed|mixed[]|string|null
     */
    private function matchArrayDimFetchValue(\PhpParser\Node\Expr $expr, \PhpParser\Node\Expr\Variable $variable)
    {
        if (!$expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        if ($expr->dim === null) {
            return null;
        }
        if (!$this->isArrayDimFetchOnVariable($expr, $variable)) {
            return null;
        }
        return $this->valueResolver->getValue($expr->dim);
    }
    private function isRequiredIsset(\PhpParser\Node\Expr\Isset_ $isset, \PhpParser\Node\Stmt\If_ $if) : bool
    {
        $issetParent = $isset->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$issetParent instanceof \PhpParser\Node\Expr\BooleanNot) {
            return \false;
        }
        return $this->betterNodeFinder->hasInstancesOf($if->stmts, [\PhpParser\Node\Expr\Throw_::class, \PhpParser\Node\Stmt\Throw_::class]);
    }
}
