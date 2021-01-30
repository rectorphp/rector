<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;

/**
 * Read-only utils for ClassConstAnalyzer Node:
 * "false, true..."
 */
final class ConstFetchManipulator
{
    public function isBool(Node $node): bool
    {
        return $this->constFetchManipulator->isTrue($node) || $this->constFetchManipulator->isFalse($node);
    }

    public function isFalse(Node $node): bool
    {
        return $this->isConstantWithLowercasedName($node, 'false');
    }

    public function isTrue(Node $node): bool
    {
        return $this->isConstantWithLowercasedName($node, 'true');
    }

    public function isNull(Node $node): bool
    {
        return $this->isConstantWithLowercasedName($node, 'null');
    }

    private function isConstantWithLowercasedName(Node $node, string $name): bool
    {
        if (! $node instanceof ConstFetch) {
            return false;
        }

        return $node->name->toLowerString() === $name;
    }
}
