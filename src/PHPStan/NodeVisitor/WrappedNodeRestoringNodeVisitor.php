<?php

declare (strict_types=1);
namespace Rector\PHPStan\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Node\Expr\AlwaysRememberedExpr;
/**
 * Restore node tree to avoid PHPStan virtual node printing
 * @ref https://github.com/phpstan/phpstan-src/commit/0cdda0b210a623ee299323da80771c2c84c602f9
 */
final class WrappedNodeRestoringNodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof AlwaysRememberedExpr) {
            return null;
        }
        $expr = $node;
        while ($expr instanceof AlwaysRememberedExpr) {
            $expr = $expr->getExpr();
        }
        return $expr;
    }
}
