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
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class ExprUsedInNodeAnalyzer
{
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @var \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer
     */
    private $usedVariableNameAnalyzer;
    /**
     * @var \Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer
     */
    private $compactFuncCallAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer $usedVariableNameAnalyzer, \Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer $compactFuncCallAnalyzer)
    {
        $this->nodeComparator = $nodeComparator;
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
    }
    public function isUsed(\PhpParser\Node $node, \PhpParser\Node\Expr $expr) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\Include_) {
            return \true;
        }
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->compactFuncCallAnalyzer->isInCompact($node, $expr);
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable) {
            return $this->usedVariableNameAnalyzer->isVariableNamed($node, $expr);
        }
        return $this->nodeComparator->areNodesEqual($node, $expr);
    }
}
