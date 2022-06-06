<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\Isset_;
use RectorPrefix20220606\PhpParser\Node\Expr\Throw_;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Throw_ as ThrowStmt;
use RectorPrefix20220606\Rector\Core\PhpParser\Comparing\NodeComparator;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\Value\ValueResolver;
use RectorPrefix20220606\Rector\NodeRemoval\NodeRemover;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
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
    /**
     * @readonly
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    public function __construct(BetterNodeFinder $betterNodeFinder, ValueResolver $valueResolver, NodeComparator $nodeComparator, NodeRemover $nodeRemover)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->valueResolver = $valueResolver;
        $this->nodeComparator = $nodeComparator;
        $this->nodeRemover = $nodeRemover;
    }
    /**
     * @return string[]
     */
    public function resolveOptionalParamNames(ClassMethod $classMethod, Variable $paramVariable) : array
    {
        $optionalParamNames = [];
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            /** @var If_ $if */
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
    public function removeArrayDimFetchIssets(ClassMethod $classMethod, Variable $paramVariable) : void
    {
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            /** @var If_ $if */
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
                $this->nodeRemover->removeNode($if);
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
        if ($expr->dim === null) {
            return null;
        }
        if (!$this->isArrayDimFetchOnVariable($expr, $variable)) {
            return null;
        }
        return $this->valueResolver->getValue($expr->dim);
    }
    private function isRequiredIsset(Isset_ $isset, If_ $if) : bool
    {
        $issetParent = $isset->getAttribute(AttributeKey::PARENT_NODE);
        if (!$issetParent instanceof BooleanNot) {
            return \false;
        }
        return $this->betterNodeFinder->hasInstancesOf($if->stmts, [Throw_::class, ThrowStmt::class]);
    }
}
