<?php

declare (strict_types=1);
namespace Rector\Nette\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
final class AlwaysTemplateParameterAssign
{
    /**
     * @var \PhpParser\Node\Expr\Assign
     */
    private $assign;
    /**
     * @var string
     */
    private $parameterName;
    /**
     * @var \PhpParser\Node\Expr
     */
    private $assignedExpr;
    public function __construct(\PhpParser\Node\Expr\Assign $assign, string $parameterName, \PhpParser\Node\Expr $assignedExpr)
    {
        $this->assign = $assign;
        $this->parameterName = $parameterName;
        $this->assignedExpr = $assignedExpr;
    }
    public function getAssign() : \PhpParser\Node\Expr\Assign
    {
        return $this->assign;
    }
    public function getAssignVar() : \PhpParser\Node\Expr
    {
        return $this->assign->var;
    }
    public function getParameterName() : string
    {
        return $this->parameterName;
    }
    public function getAssignedExpr() : \PhpParser\Node\Expr
    {
        return $this->assignedExpr;
    }
}
