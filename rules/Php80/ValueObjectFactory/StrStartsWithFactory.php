<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObjectFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Php80\ValueObject\StrStartsWith;
final class StrStartsWithFactory
{
    public function createFromFuncCall(FuncCall $funcCall, bool $isPositive): ?StrStartsWith
    {
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        if (count($funcCall->getArgs()) < 2) {
            return null;
        }
        $firstArg = $funcCall->getArg('string1', 0);
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $secondArg = $funcCall->getArg('string2', 1);
        if (!$secondArg instanceof Arg) {
            return null;
        }
        $haystack = $firstArg->value;
        $needle = $secondArg->value;
        return new StrStartsWith($funcCall, $haystack, $needle, $isPositive);
    }
}
