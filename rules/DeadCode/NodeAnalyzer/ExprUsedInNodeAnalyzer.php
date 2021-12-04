<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
final class ExprUsedInNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer
     */
    private $usedVariableNameAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer
     */
    private $compactFuncCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer $usedVariableNameAnalyzer, \Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer $compactFuncCallAnalyzer, \Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter)
    {
        $this->nodeComparator = $nodeComparator;
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    public function isUsed(\PhpParser\Node $node, \PhpParser\Node\Expr $expr) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\Include_) {
            return \true;
        }
        // variable as variable variable need mark as used
        if ($node instanceof \PhpParser\Node\Expr\Variable && $expr instanceof \PhpParser\Node\Expr\Variable) {
            $print = $this->betterStandardPrinter->print($node);
            if (\strncmp($print, '${$', \strlen('${$')) === 0) {
                return \true;
            }
        }
        if ($node instanceof \PhpParser\Node\Expr\FuncCall && $expr instanceof \PhpParser\Node\Expr\Variable) {
            return $this->compactFuncCallAnalyzer->isInCompact($node, $expr);
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable) {
            return $this->usedVariableNameAnalyzer->isVariableNamed($node, $expr);
        }
        return $this->nodeComparator->areNodesEqual($node, $expr);
    }
}
