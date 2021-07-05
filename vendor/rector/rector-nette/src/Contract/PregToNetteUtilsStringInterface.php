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
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical $identical
     */
    public function refactorIdentical($identical) : ?\PhpParser\Node\Expr\Cast\Bool_;
    /**
     * @return FuncCall|StaticCall|Assign|null
     * @param \PhpParser\Node\Expr\FuncCall $funcCall
     */
    public function refactorFuncCall($funcCall) : ?\PhpParser\Node\Expr;
}
