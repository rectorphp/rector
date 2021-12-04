<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
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

    /**
     * $isCheckNameScope parameter is used to whether to check scope of Name that may be renamed
     * @see https://github.com/rectorphp/rector/issues/6675
     */
    public function isUsed(Expr $expr, bool $isCheckNameScope = false): bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext(
            $expr,
            function (Node $node) use ($expr, $isCheckNameScope): bool {
                if ($isCheckNameScope && $node instanceof Name) {
                    $scope = $node->getAttribute(AttributeKey::SCOPE);
                    $resolvedName = $node->getAttribute(AttributeKey::RESOLVED_NAME);
                    $next = $node->getAttribute(AttributeKey::NEXT_NODE);

                    if (! $scope instanceof Scope && ! $resolvedName instanceof Name && $next instanceof Arg) {
                        return true;
                    }
                }

                if (! $node instanceof If_) {
                    return $this->exprUsedInNodeAnalyzer->isUsed($node, $expr);
                }

                /**
                 * handle when used along with RemoveAlwaysElseRector
                 */
                if (! $this->hasIfChangedByRemoveAlwaysElseRector($node)) {
                    return $this->exprUsedInNodeAnalyzer->isUsed($node, $expr);
                }

                return true;
            }
        );
    }

    private function hasIfChangedByRemoveAlwaysElseRector(If_ $if): bool
    {
        $createdByRule = $if->getAttribute(AttributeKey::CREATED_BY_RULE);
        return $createdByRule === RemoveAlwaysElseRector::class;
    }
}
