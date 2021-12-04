<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\NodeNameResolver\NodeNameResolver;

final class CompactFuncCallAnalyzer
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver
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
     * @param array<int, Arg|VariadicPlaceholder|ArrayItem|null> $nodes
     */
    private function isInArgOrArrayItemNodes(array $nodes, string $variableName): bool
    {
        foreach ($nodes as $node) {
            if ($this->shouldSkip($node)) {
                continue;
            }

            /** @var Arg|ArrayItem $node */
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

    private function shouldSkip(Arg|VariadicPlaceholder|ArrayItem|null $node): bool
    {
        if ($node === null) {
            return true;
        }

        return $node instanceof VariadicPlaceholder;
    }
}
