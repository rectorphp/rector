<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeTraverser;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

final class FluentReplaceRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Return_) {
            return false;
        }

        $returnExpr = $node->expr;

        if (! $returnExpr instanceof Variable) {
            return false;
        }

        return $returnExpr->name === 'this';
    }

    public function refactor(Node $node): ?Node
    {
        $this->removeNode = true;
        return null;
    }
}
