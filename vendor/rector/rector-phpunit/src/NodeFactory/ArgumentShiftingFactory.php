<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPUnit\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
final class ArgumentShiftingFactory
{
    public function removeAllButFirstArgMethodCall(MethodCall $methodCall, string $methodName) : void
    {
        $methodCall->name = new Identifier($methodName);
        foreach (\array_keys($methodCall->args) as $i) {
            // keep first arg
            if ($i === 0) {
                continue;
            }
            unset($methodCall->args[$i]);
        }
    }
}
