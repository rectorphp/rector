<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;

/**
 * Read-only utils for FuncCall Node:
 * "someMethod()"
 */
final class FuncCallAnalyzer
{
    /**
     * Checks "specificFunction()"
     */
    public function isName(Node $node, string $name): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $node->name instanceof Name) {
            return false;
        }

        return (string) $node->name === $name;
    }
}
