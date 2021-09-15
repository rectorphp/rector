<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
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
                if (!$scope instanceof \PHPStan\Analyser\Scope && !$resolvedName instanceof \PhpParser\Node\Name) {
                    return \true;
                }
            }
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $expr);
        });
    }
}
