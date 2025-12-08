<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\DeadCode\ConditionResolver;
use Rector\DeadCode\ValueObject\VersionCompareCondition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\NodeTraverser\SimpleNodeTraverser;
final class PhpVersionConditionNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    /**
     * @readonly
     */
    private ConditionResolver $conditionResolver;
    public function __construct(ConditionResolver $conditionResolver)
    {
        $this->conditionResolver = $conditionResolver;
    }
    public function enterNode(Node $node): ?Node
    {
        if (($node instanceof Ternary || $node instanceof If_) && $this->hasVersionCompareCond($node)) {
            if ($node instanceof Ternary) {
                $nodes = [$node->else];
                if ($node->if instanceof Node) {
                    $nodes[] = $node->if;
                }
            } else {
                $nodes = $node->stmts;
            }
            SimpleNodeTraverser::decorateWithAttributeValue($nodes, AttributeKey::PHP_VERSION_CONDITIONED, \true);
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Expr\Ternary $ifOrTernary
     */
    private function hasVersionCompareCond($ifOrTernary): bool
    {
        if (!$ifOrTernary->cond instanceof FuncCall) {
            return \false;
        }
        $versionCompare = $this->conditionResolver->resolveFromExpr($ifOrTernary->cond);
        return $versionCompare instanceof VersionCompareCondition;
    }
}
