<?php

declare (strict_types=1);
namespace Rector\Php74\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class ClosureArrowFunctionAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    public function matchArrowFunctionExpr(\PhpParser\Node\Expr\Closure $closure) : ?\PhpParser\Node\Expr
    {
        if (\count($closure->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $closure->stmts[0];
        if (!$onlyStmt instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        /** @var Return_ $return */
        $return = $onlyStmt;
        if ($return->expr === null) {
            return null;
        }
        if ($this->shouldSkipForUsedReferencedValue($closure, $return->expr)) {
            return null;
        }
        return $return->expr;
    }
    private function shouldSkipForUsedReferencedValue(\PhpParser\Node\Expr\Closure $closure, \PhpParser\Node\Expr $expr) : bool
    {
        $referencedValues = $this->resolveReferencedUseVariablesFromClosure($closure);
        if ($referencedValues === []) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirst([$expr], function (\PhpParser\Node $node) use($referencedValues) : bool {
            foreach ($referencedValues as $referencedValue) {
                if ($this->nodeComparator->areNodesEqual($node, $referencedValue)) {
                    return \true;
                }
            }
            return \false;
        });
    }
    /**
     * @return Variable[]
     */
    private function resolveReferencedUseVariablesFromClosure(\PhpParser\Node\Expr\Closure $closure) : array
    {
        $referencedValues = [];
        /** @var ClosureUse $use */
        foreach ($closure->uses as $use) {
            if ($use->byRef) {
                $referencedValues[] = $use->var;
            }
        }
        return $referencedValues;
    }
}
