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
     * @param FuncCall|StaticCall|MethodCall $call
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function __construct(
        private Variable $variable,
        private Expr $call,
        private string $variableName,
        private FunctionLike $functionLike
    ) {
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
