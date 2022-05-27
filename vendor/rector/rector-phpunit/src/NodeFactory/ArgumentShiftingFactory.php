<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
final class ArgumentShiftingFactory
{
    public function removeAllButFirstArgMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall, string $methodName) : void
    {
        $methodCall->name = new \PhpParser\Node\Identifier($methodName);
        foreach (\array_keys($methodCall->args) as $i) {
            // keep first arg
            if ($i === 0) {
                continue;
            }
            unset($methodCall->args[$i]);
        }
    }
}
