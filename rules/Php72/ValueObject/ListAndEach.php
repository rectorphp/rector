<?php

declare (strict_types=1);
namespace Rector\Php72\ValueObject;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
final class ListAndEach
{
    /**
     * @readonly
     */
    private List_ $list;
    /**
     * @readonly
     */
    private FuncCall $eachFuncCall;
    public function __construct(List_ $list, FuncCall $eachFuncCall)
    {
        $this->list = $list;
        $this->eachFuncCall = $eachFuncCall;
    }
    public function getList() : List_
    {
        return $this->list;
    }
    public function getEachFuncCall() : FuncCall
    {
        return $this->eachFuncCall;
    }
}
