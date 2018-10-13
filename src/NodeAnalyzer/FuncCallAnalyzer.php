<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;

/**
 * Read-only utils for FuncCall Node:
 * "someMethod()"
 */
final class FuncCallAnalyzer
{
    /**
     * @var CallAnalyzer
     */
    private $callAnalyzer;

    public function __construct(CallAnalyzer $callAnalyzer)
    {
        $this->callAnalyzer = $callAnalyzer;
    }

    /**
     * Checks "specificFunction()"
     */
    public function isName(Node $node, string $name): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        return $this->callAnalyzer->resolveName($node) === $name;
    }
}
