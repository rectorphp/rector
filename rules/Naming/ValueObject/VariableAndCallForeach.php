<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
final class VariableAndCallForeach
{
    /**
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @var \PhpParser\Node\Expr
     */
    private $call;
    /**
     * @var string
     */
    private $variableName;
    /**
     * @var \PhpParser\Node\FunctionLike
     */
    private $functionLike;
    /**
     * @param FuncCall|StaticCall|MethodCall $call
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function __construct(\PhpParser\Node\Expr\Variable $variable, \PhpParser\Node\Expr $call, string $variableName, \PhpParser\Node\FunctionLike $functionLike)
    {
        $this->variable = $variable;
        $this->call = $call;
        $this->variableName = $variableName;
        $this->functionLike = $functionLike;
    }
    public function getVariable() : \PhpParser\Node\Expr\Variable
    {
        return $this->variable;
    }
    /**
     * @return FuncCall|StaticCall|MethodCall
     */
    public function getCall() : \PhpParser\Node\Expr
    {
        return $this->call;
    }
    public function getVariableName() : string
    {
        return $this->variableName;
    }
    /**
     * @return ClassMethod|Function_|Closure
     */
    public function getFunctionLike() : \PhpParser\Node\FunctionLike
    {
        return $this->functionLike;
    }
}
