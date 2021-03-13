<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;

final class ArgumentShiftingFactory
{
    public function createFromMethodCall(MethodCall $methodCall, string $methodName): MethodCall
    {
        $methodCall->name = new Identifier($methodName);
        foreach (array_keys($methodCall->args) as $i) {
            // keep first arg
            if ($i === 0) {
                continue;
            }

            unset($methodCall->args[$i]);
        }

        return $methodCall;
    }
}
