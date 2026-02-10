<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
final class VariableAndDimFetch
{
    /**
     * @readonly
     */
    private Variable $variable;
    /**
     * @readonly
     */
    private Expr $dimFetchExpr;
    public function __construct(Variable $variable, Expr $dimFetchExpr)
    {
        $this->variable = $variable;
        $this->dimFetchExpr = $dimFetchExpr;
    }
    public function getVariable(): Variable
    {
        return $this->variable;
    }
    public function getDimFetchExpr(): Expr
    {
        return $this->dimFetchExpr;
    }
}
