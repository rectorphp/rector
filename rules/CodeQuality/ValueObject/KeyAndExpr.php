<?php

declare (strict_types=1);
namespace Rector\CodeQuality\ValueObject;

use PhpParser\Node\Expr;
final class KeyAndExpr
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $keyExpr;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    public function __construct(Expr $keyExpr, Expr $expr)
    {
        $this->keyExpr = $keyExpr;
        $this->expr = $expr;
    }
    public function getKeyExpr() : Expr
    {
        return $this->keyExpr;
    }
    public function getExpr() : Expr
    {
        return $this->expr;
    }
}
