<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic\ValueObject;

use PhpParser\Node\Expr;
final class ClassAndMethodArrayExprs
{
    /**
     * @readonly
     */
    private Expr $classExpr;
    /**
     * @readonly
     */
    private Expr $methodExpr;
    public function __construct(Expr $classExpr, Expr $methodExpr)
    {
        $this->classExpr = $classExpr;
        $this->methodExpr = $methodExpr;
    }
    public function getClassExpr(): Expr
    {
        return $this->classExpr;
    }
    public function getMethodExpr(): Expr
    {
        return $this->methodExpr;
    }
}
