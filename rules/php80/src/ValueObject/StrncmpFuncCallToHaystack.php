<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\FuncCall;

final class StrncmpFuncCallToHaystack
{
    /**
     * @var FuncCall
     */
    private $strncmpFuncCall;

    /**
     * @var bool
     */
    private $isPositive = false;

    public function __construct(FuncCall $strncmpFuncCall, bool $isPositive)
    {
        $this->strncmpFuncCall = $strncmpFuncCall;
        $this->isPositive = $isPositive;
    }

    public function getStrncmpFuncCall(): FuncCall
    {
        return $this->strncmpFuncCall;
    }

    public function isPositive(): bool
    {
        return $this->isPositive;
    }
}
