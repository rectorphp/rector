<?php

declare (strict_types=1);
namespace Rector\CodeQuality\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
final class DefaultPropertyExprAssign
{
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\Expression
     */
    private $assignExpression;
    /**
     * @readonly
     * @var string
     */
    private $propertyName;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $defaultExpr;
    public function __construct(\PhpParser\Node\Stmt\Expression $assignExpression, string $propertyName, \PhpParser\Node\Expr $defaultExpr)
    {
        $this->assignExpression = $assignExpression;
        $this->propertyName = $propertyName;
        $this->defaultExpr = $defaultExpr;
    }
    public function getAssignExpression() : \PhpParser\Node\Stmt\Expression
    {
        return $this->assignExpression;
    }
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
    public function getDefaultExpr() : \PhpParser\Node\Expr
    {
        return $this->defaultExpr;
    }
}
