<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr;
final class CondAndExpr
{
    /**
     * @var mixed[]
     */
    private $condExprs;
    /**
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    /**
     * @var string
     */
    private $kind;
    /**
     * @param Expr[] $condExprs
     */
    public function __construct(array $condExprs, \PhpParser\Node\Expr $expr, string $kind)
    {
        $this->condExprs = $condExprs;
        $this->expr = $expr;
        $this->kind = $kind;
    }
    public function getExpr() : \PhpParser\Node\Expr
    {
        return $this->expr;
    }
    /**
     * @return Expr[]
     */
    public function getCondExprs() : array
    {
        return $this->condExprs;
    }
    public function getKind() : string
    {
        return $this->kind;
    }
}
