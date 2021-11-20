<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    /**
     * $isCheckNameScope parameter is used to whether to check scope of Name that may be renamed
     * @see https://github.com/rectorphp/rector/issues/6675
     */
    public function isUsed(\PhpParser\Node\Expr $expr, bool $isCheckNameScope = \false) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext($expr, function (\PhpParser\Node $node) use($expr, $isCheckNameScope) : bool {
            if ($isCheckNameScope && $node instanceof \PhpParser\Node\Name) {
                $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
                $resolvedName = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::RESOLVED_NAME);
                $next = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
                if (!$scope instanceof \PHPStan\Analyser\Scope && !$resolvedName instanceof \PhpParser\Node\Name && $next instanceof \PhpParser\Node\Arg) {
                    return \true;
                }
            }
            if (!$node instanceof \PhpParser\Node\Stmt\If_) {
                return $this->exprUsedInNodeAnalyzer->isUsed($node, $expr);
            }
            /**
             * handle when used along with RemoveAlwaysElseRector
             */
            if (!$this->hasIfChangedByRemoveAlwaysElseRector($node)) {
                return $this->exprUsedInNodeAnalyzer->isUsed($node, $expr);
            }
            return \true;
        });
    }
    private function hasIfChangedByRemoveAlwaysElseRector(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        $createdByRule = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE);
        return $createdByRule === \Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector::class;
    }
}
