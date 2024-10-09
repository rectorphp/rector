<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit100\NodeDecorator;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
final class WillReturnPerIfNodeDecorator
{
    public function decorate(Closure $callbackClosure, ?MethodCall $willReturnOnConsecutiveMethodCall) : void
    {
        if ($willReturnOnConsecutiveMethodCall instanceof MethodCall) {
            foreach ($callbackClosure->stmts as $key => $stmt) {
                if ($stmt instanceof If_) {
                    $currentArg = $willReturnOnConsecutiveMethodCall->getArgs()[$key];
                    $stmt->stmts[] = new Return_($currentArg->value);
                }
            }
        }
    }
}
