<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
final class VariableAndExprAssign
{
    /**
     * @readonly
     */
    private Variable $variable;
    /**
     * @readonly
     */
    private Expr $expr;
    public function __construct(Variable $variable, Expr $expr)
    {
        $this->variable = $variable;
        $this->expr = $expr;
    }
    public function getVariable(): Variable
    {
        return $this->variable;
    }
    public function getExpr(): Expr
    {
        return $this->expr;
    }
}
