<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;
use Rector\NodeAnalyzer\CompactFuncCallAnalyzer;
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
    public function __construct(\Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer $usedVariableNameAnalyzer, CompactFuncCallAnalyzer $compactFuncCallAnalyzer)
    {
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
    }
    public function isUsed(Node $node, Variable $variable) : bool
    {
        if ($node instanceof Include_) {
            return \true;
        }
        // variable as variable variable need mark as used
        if ($node instanceof Variable && $node->name instanceof Expr) {
            return \true;
        }
        if ($node instanceof FuncCall) {
            return $this->compactFuncCallAnalyzer->isInCompact($node, $variable);
        }
        return $this->usedVariableNameAnalyzer->isVariableNamed($node, $variable);
    }
}
