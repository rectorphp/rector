<?php

declare (strict_types=1);
namespace Rector\Nette\Contract;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
interface PregToNetteUtilsStringInterface
{
    public function refactorIdentical(\PhpParser\Node\Expr\BinaryOp\Identical $identical) : ?\PhpParser\Node\Expr\Cast\Bool_;
    /**
     * @return FuncCall|StaticCall|Assign|null
     */
    public function refactorFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr;
}
