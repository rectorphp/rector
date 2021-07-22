<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;

final class ExprUsedInNodeAnalyzer
{
    public function __construct(
        private NodeComparator $nodeComparator,
        private UsedVariableNameAnalyzer $usedVariableNameAnalyzer,
        private CompactFuncCallAnalyzer $compactFuncCallAnalyzer,
        private NodeNameResolver $nodeNameResolver,
        private BetterStandardPrinter $betterStandardPrinter
    ) {
    }

    public function isUsed(Node $node, Expr $expr): bool
    {
        if ($node instanceof Include_) {
            return true;
        }

        // variable as variable variable need mark as used
        if ($node instanceof Variable && $expr instanceof Variable) {
            $print = $this->betterStandardPrinter->print($node);
            if (\str_starts_with($print, '${$')) {
                return true;
            }
        }

        if ($node instanceof FuncCall && $expr instanceof Variable) {
            return $this->compactFuncCallAnalyzer->isInCompact($node, $expr);
        }

        if ($expr instanceof Variable) {
            return $this->usedVariableNameAnalyzer->isVariableNamed($node, $expr);
        }

        return $this->nodeComparator->areNodesEqual($node, $expr);
    }
}
