<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\UseUse;
use Rector\Rector\AbstractRector;

/**
 * Covers https://github.com/nikic/PHP-Parser/commit/3da189769cfa19dabd890b85e1a4bfe63cfcc7fb
 */
final class UseWithAliasRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof UseUse) {
            return false;
        }

        dump($node);
        die;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
    }
}
