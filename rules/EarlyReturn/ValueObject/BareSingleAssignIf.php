<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\If_;
final class BareSingleAssignIf
{
    /**
     * @readonly
     */
    private If_ $if;
    /**
     * @readonly
     */
    private Assign $assign;
    public function __construct(If_ $if, Assign $assign)
    {
        $this->if = $if;
        $this->assign = $assign;
    }
    public function getIfCondExpr() : Expr
    {
        return $this->if->cond;
    }
    public function getIf() : If_
    {
        return $this->if;
    }
    public function getAssign() : Assign
    {
        return $this->assign;
    }
}
