<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;

/**
 * Read-only utils for ClassConstAnalyzer Node:
 * "false, true..."
 */
final class ConstFetchAnalyzer
{
    public function isFalse(Node $node): bool
    {
        if (! $node instanceof ConstFetch) {
            return false;
        }

        return $node->name->toLowerString() === 'false';
    }

    public function isTrue(Node $node): bool
    {
        if (! $node instanceof ConstFetch) {
            return false;
        }

        return $node->name->toLowerString() === 'true';
    }

    public function isBool(Node $node): bool
    {
        return $this->isTrue($node) || $this->isFalse($node);
    }
}
