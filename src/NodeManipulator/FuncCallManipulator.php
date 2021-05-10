<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Expr\FuncCall;
use Rector\Core\PhpParser\Node\Value\ValueResolver;

final class FuncCallManipulator
{
    public function __construct(
        private ValueResolver $valueResolver
    ) {
    }

    /**
     * @param FuncCall[] $compactFuncCalls
     * @return string[]
     */
    public function extractArgumentsFromCompactFuncCalls(array $compactFuncCalls): array
    {
        $arguments = [];
        foreach ($compactFuncCalls as $compactFuncCall) {
            foreach ($compactFuncCall->args as $arg) {
                $value = $this->valueResolver->getValue($arg->value);
                if ($value === null) {
                    continue;
                }

                $arguments[] = $value;
            }
        }

        return $arguments;
    }
}
