<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\NodeNameResolver\NodeNameResolver;

final class CompactFuncCallAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function isInCompact(FuncCall $funcCall, Variable $variable): bool
    {
        if (! $this->nodeNameResolver->isName($funcCall, 'compact')) {
            return false;
        }

        $variableName = $variable->name;
        if (! is_string($variableName)) {
            return false;
        }

        return $this->isInArgOrArrayItemNodes($funcCall->args, $variableName);
    }

    /**
     * @param array<int, \PhpParser\Node\Arg|\PhpParser\Node\Expr\ArrayItem|null> $nodes
     */
    private function isInArgOrArrayItemNodes(array $nodes, string $variableName): bool
    {
        foreach ($nodes as $node) {
            if ($node === null) {
                continue;
            }

            if ($node->value instanceof Array_) {
                if ($this->isInArgOrArrayItemNodes($node->value->items, $variableName)) {
                    return true;
                }

                continue;
            }

            if (! $node->value instanceof String_) {
                continue;
            }

            if ($node->value->value === $variableName) {
                return true;
            }
        }

        return false;
    }
}
