<?php

declare(strict_types=1);

namespace Rector\CodingStyle\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;

final class SpreadVariablesCollector
{
    /**
     * @param Param[]|Arg[] $nodes
     * @return Param[]|Arg[]
     */
    public function getSpreadVariables(array $nodes): array
    {
        $spreadVariables = [];

        foreach ($nodes as $key => $paramOrArg) {
            if ($this->isVariadicParam($paramOrArg)) {
                $spreadVariables[$key] = $paramOrArg;
            }

            if ($this->isVariadicArg($paramOrArg)) {
                $spreadVariables[$key] = $paramOrArg;
            }
        }

        return $spreadVariables;
    }

    private function isVariadicParam(Node $node): bool
    {
        if (! $node instanceof Param) {
            return false;
        }

        return $node->variadic && $node->type === null;
    }

    private function isVariadicArg(Node $node): bool
    {
        if (! $node instanceof Arg) {
            return false;
        }

        return $node->unpack && $node->value instanceof Variable;
    }
}
