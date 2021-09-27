<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObjectFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Php80\ValueObject\StrStartsWith;
final class StrStartsWithFactory
{
    /**
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function createFromFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall, bool $isPositive) : ?\Rector\Php80\ValueObject\StrStartsWith
    {
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($funcCall->args, [0, 1])) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = $funcCall->args[0];
        $haystack = $firstArg->value;
        /** @var Arg $secondArg */
        $secondArg = $funcCall->args[1];
        $needle = $secondArg->value;
        return new \Rector\Php80\ValueObject\StrStartsWith($funcCall, $haystack, $needle, $isPositive);
    }
}
