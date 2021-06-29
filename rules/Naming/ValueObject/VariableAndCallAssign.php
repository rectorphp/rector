<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
final class VariableAndCallAssign
{
    /**
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @var \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall
     */
    private $expr;
    /**
     * @var \PhpParser\Node\Expr\Assign
     */
    private $assign;
    /**
     * @var string
     */
    private $variableName;
    /**
     * @var \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure
     */
    private $functionLike;
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $expr
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function __construct(\PhpParser\Node\Expr\Variable $variable, $expr, \PhpParser\Node\Expr\Assign $assign, string $variableName, $functionLike)
    {
        $this->variable = $variable;
        $this->expr = $expr;
        $this->assign = $assign;
        $this->variableName = $variableName;
        $this->functionLike = $functionLike;
    }
    public function getVariable() : \PhpParser\Node\Expr\Variable
    {
        return $this->variable;
    }
    /**
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    public function getCall()
    {
        return $this->expr;
    }
    public function getVariableName() : string
    {
        return $this->variableName;
    }
    /**
     * @return \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    public function getFunctionLike()
    {
        return $this->functionLike;
    }
    public function getAssign() : \PhpParser\Node\Expr\Assign
    {
        return $this->assign;
    }
}
