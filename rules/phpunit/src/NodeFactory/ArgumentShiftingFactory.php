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
        foreach (array_keys($methodCall->args) as $array_key) {
            // keep first arg
            if ($array_key === 0) {
                continue;
            }

            unset($methodCall->args[$array_key]);
        }

        return $methodCall;
    }
}
