<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ExprUsedInNextNodeAnalyzer
{
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer
    ) {
    }

    public function isUsed(Expr $expr): bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext(
            $expr,
            function (Node $node) use ($expr): bool {
                $isUsed = $this->exprUsedInNodeAnalyzer->isUsed($node, $expr);

                if ($isUsed) {
                    return true;
                }

                /**
                 * handle when used along with RemoveAlwaysElseRector
                 */
                return $node instanceof If_ && $this->hasIfChangedByRemoveAlwaysElseRector($node);
            }
        );
    }

    private function hasIfChangedByRemoveAlwaysElseRector(If_ $if): bool
    {
        $createdByRule = $if->getAttribute(AttributeKey::CREATED_BY_RULE);
        return $createdByRule === RemoveAlwaysElseRector::class;
    }
}
