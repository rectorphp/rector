<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    public function isUsed(\PhpParser\Node\Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext($expr, function (\PhpParser\Node $node) use($expr) : bool {
            $isUsed = $this->exprUsedInNodeAnalyzer->isUsed($node, $expr);
            if ($isUsed) {
                return \true;
            }
            /**
             * handle when used along with RemoveAlwaysElseRector
             */
            return $node instanceof \PhpParser\Node\Stmt\If_ && $this->hasIfChangedByRemoveAlwaysElseRector($node);
        });
    }
    private function hasIfChangedByRemoveAlwaysElseRector(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        $createdByRule = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE) ?? [];
        return \in_array(\Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector::class, $createdByRule, \true);
    }
}
