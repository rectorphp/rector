<?php

declare (strict_types=1);
namespace Rector\Php74\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Util\ArrayChecker;
final class ClosureArrowFunctionAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Util\ArrayChecker
     */
    private $arrayChecker;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, ArrayChecker $arrayChecker)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->arrayChecker = $arrayChecker;
    }
    public function matchArrowFunctionExpr(Closure $closure) : ?Expr
    {
        if (\count($closure->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $closure->stmts[0];
        if (!$onlyStmt instanceof Return_) {
            return null;
        }
        /** @var Return_ $return */
        $return = $onlyStmt;
        if (!$return->expr instanceof Expr) {
            return null;
        }
        if ($this->shouldSkipForUsedReferencedValue($closure)) {
            return null;
        }
        return $return->expr;
    }
    private function shouldSkipForUsedReferencedValue(Closure $closure) : bool
    {
        $referencedValues = $this->resolveReferencedUseVariablesFromClosure($closure);
        if ($referencedValues === []) {
            return \false;
        }
        $isFoundInStmt = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($closure, function (Node $node) use($referencedValues) : bool {
            foreach ($referencedValues as $referencedValue) {
                if ($this->nodeComparator->areNodesEqual($node, $referencedValue)) {
                    return \true;
                }
            }
            return \false;
        });
        if ($isFoundInStmt) {
            return \true;
        }
        return $this->isFoundInInnerUses($closure, $referencedValues);
    }
    /**
     * @param Variable[] $referencedValues
     */
    private function isFoundInInnerUses(Closure $node, array $referencedValues) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, function (Node $subNode) use($referencedValues) : bool {
            if (!$subNode instanceof Closure) {
                return \false;
            }
            foreach ($referencedValues as $referencedValue) {
                $isFoundInInnerUses = $this->arrayChecker->doesExist($subNode->uses, function (ClosureUse $closureUse) use($referencedValue) : bool {
                    return $closureUse->byRef && $this->nodeComparator->areNodesEqual($closureUse->var, $referencedValue);
                });
                if ($isFoundInInnerUses) {
                    return \true;
                }
            }
            return \false;
        });
    }
    /**
     * @return Variable[]
     */
    private function resolveReferencedUseVariablesFromClosure(Closure $closure) : array
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
