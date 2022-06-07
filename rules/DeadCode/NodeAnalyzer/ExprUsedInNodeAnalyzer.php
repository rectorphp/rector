<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
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
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(NodeComparator $nodeComparator, \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer $usedVariableNameAnalyzer, CompactFuncCallAnalyzer $compactFuncCallAnalyzer, NodePrinterInterface $nodePrinter)
    {
        $this->nodeComparator = $nodeComparator;
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
        $this->nodePrinter = $nodePrinter;
    }
    public function isUsed(Node $node, Expr $expr) : bool
    {
        if ($node instanceof Include_) {
            return \true;
        }
        // variable as variable variable need mark as used
        if ($node instanceof Variable && $expr instanceof Variable) {
            $print = $this->nodePrinter->print($node);
            if (\strncmp($print, '${$', \strlen('${$')) === 0) {
                return \true;
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
