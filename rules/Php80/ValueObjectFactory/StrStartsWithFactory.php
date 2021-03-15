<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObjectFactory;

use PhpParser\Node\Expr\FuncCall;
use Rector\Php80\ValueObject\StrStartsWith;

final class StrStartsWithFactory
{
    public function createFromFuncCall(FuncCall $funcCall, bool $isPositive): StrStartsWith
    {
        $haystack = $funcCall->args[0]->value;
        $needle = $funcCall->args[1]->value;

        return new StrStartsWith($funcCall, $haystack, $needle, $isPositive);
    }
}
