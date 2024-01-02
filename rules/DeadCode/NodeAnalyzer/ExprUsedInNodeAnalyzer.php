<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;
use Rector\NodeAnalyzer\CompactFuncCallAnalyzer;
use Rector\PhpParser\Printer\BetterStandardPrinter;
final class ExprUsedInNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer
     */
    private $usedVariableNameAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\CompactFuncCallAnalyzer
     */
    private $compactFuncCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    public function __construct(\Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer $usedVariableNameAnalyzer, CompactFuncCallAnalyzer $compactFuncCallAnalyzer, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    public function isUsed(Node $node, Variable $variable) : bool
    {
        if ($node instanceof Include_) {
            return \true;
        }
        // variable as variable variable need mark as used
        if ($node instanceof Variable) {
            $print = $this->betterStandardPrinter->print($node);
            if (\strncmp($print, '${$', \strlen('${$')) === 0) {
                return \true;
            }
        }
        if ($node instanceof FuncCall) {
            return $this->compactFuncCallAnalyzer->isInCompact($node, $variable);
        }
        return $this->usedVariableNameAnalyzer->isVariableNamed($node, $variable);
    }
}
