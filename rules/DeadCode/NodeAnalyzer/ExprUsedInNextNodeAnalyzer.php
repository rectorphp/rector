<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class ExprUsedInNextNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    public function isUsed(Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext($variable, function (Node $node) use($variable) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $variable);
        });
    }
}
