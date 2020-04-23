<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\FuncCall;

final class StrposFuncCallToZero
{
    /**
     * @var FuncCall
     */
    private $strposFuncCall;

    public function __construct(FuncCall $strposFuncCall)
    {
        $this->strposFuncCall = $strposFuncCall;
    }

    public function getStrposFuncCall(): FuncCall
    {
        return $this->strposFuncCall;
    }
}
