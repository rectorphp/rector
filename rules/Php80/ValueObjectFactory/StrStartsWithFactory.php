<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php80\ValueObjectFactory;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use RectorPrefix20220606\Rector\Php80\ValueObject\StrStartsWith;
final class StrStartsWithFactory
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function createFromFuncCall(FuncCall $funcCall, bool $isPositive) : ?StrStartsWith
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
        return new StrStartsWith($funcCall, $haystack, $needle, $isPositive);
    }
}
