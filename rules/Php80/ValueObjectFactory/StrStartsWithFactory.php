<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObjectFactory;

use PhpParser\Node\Expr\FuncCall;
use Rector\Php80\ValueObject\StrStartsWith;
final class StrStartsWithFactory
{
    public function createFromFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall, bool $isPositive) : \Rector\Php80\ValueObject\StrStartsWith
    {
        $haystack = $funcCall->args[0]->value;
        $needle = $funcCall->args[1]->value;
        return new \Rector\Php80\ValueObject\StrStartsWith($funcCall, $haystack, $needle, $isPositive);
    }
}
