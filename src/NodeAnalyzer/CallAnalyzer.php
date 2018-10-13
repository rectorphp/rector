<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

/**
 * Take cares of common logic for:
 * - @see \PhpParser\Node\Expr\StaticCall
 * - @see \PhpParser\Node\Expr\FuncCall
 * - @see \PhpParser\Node\Expr\MethodCall
 */
final class CallAnalyzer
{
    /**
     * @param StaticCall|FuncCall|MethodCall $node
     */
    public function isName(Node $node, string $name): bool
    {
        return $this->resolveName($node) === $name;
    }

    /**
     * @param StaticCall|FuncCall|MethodCall $node
     * @param string[] $names
     */
    public function isNames(Node $node, array $names): bool
    {
        return in_array($this->resolveName($node), $names, true);
    }

    /**
     * @param StaticCall|FuncCall|MethodCall $node
     */
    public function resolveName(Node $node): ?string
    {
        // namespaced (e.g. functions)
        if ($node->name instanceof Name) {
            return $node->name->toString();
        }

        // non-namespaced
        if ($node->name instanceof Identifier) {
            return $node->name->toString();
        }

        // unable to resolve
        return null;
    }
}
