<?php

declare(strict_types=1);

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
     * @var string
     */
    private $variableName;

    /**
     * @var Variable
     */
    private $variable;

    /**
     * @var FuncCall|MethodCall|StaticCall
     */
    private $call;

    /**
     * @var ClassMethod|Function_|Closure
     */
    private $functionLike;

    /**
     * @param FuncCall|StaticCall|MethodCall $expr
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function __construct(Variable $variable, Expr $expr, string $variableName, FunctionLike $functionLike)
    {
        $this->variable = $variable;
        $this->call = $expr;
        $this->variableName = $variableName;
        $this->functionLike = $functionLike;
    }

    public function getVariable(): Variable
    {
        return $this->variable;
    }

    /**
     * @return FuncCall|StaticCall|MethodCall
     */
    public function getCall(): Expr
    {
        return $this->call;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    /**
     * @return ClassMethod|Function_|Closure
     */
    public function getFunctionLike(): FunctionLike
    {
        return $this->functionLike;
    }
}
