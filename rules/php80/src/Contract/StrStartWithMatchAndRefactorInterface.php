<?php

declare(strict_types=1);

namespace Rector\Php80\Contract;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use Rector\Php80\ValueObject\StrStartsWithValueObject;

interface StrStartWithMatchAndRefactorInterface
{
    public function match(BinaryOp $binaryOp): ?StrStartsWithValueObject;

    /**
     * @return FuncCall|BooleanNot|null
     */
    public function refactor(StrStartsWithValueObject $strStartsWithValueObject): ?Node;
}
