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
    public function __construct(\PhpParser\Node\Expr $keyExpr, \PhpParser\Node\Expr $expr)
    {
        $this->keyExpr = $keyExpr;
        $this->expr = $expr;
    }
    public function getKeyExpr() : \PhpParser\Node\Expr
    {
        return $this->keyExpr;
    }
    public function getExpr() : \PhpParser\Node\Expr
    {
        return $this->expr;
    }
}
