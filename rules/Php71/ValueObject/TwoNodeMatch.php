<?php

declare (strict_types=1);
namespace Rector\Php71\ValueObject;

use PhpParser\Node\Expr;
final class TwoNodeMatch
{
    /**
     * @var Expr
     */
    private $firstExpr;
    /**
     * @var Expr
     */
    private $secondExpr;
    public function __construct(\PhpParser\Node\Expr $firstExpr, \PhpParser\Node\Expr $secondExpr)
    {
        $this->firstExpr = $firstExpr;
        $this->secondExpr = $secondExpr;
    }
    public function getFirstExpr() : \PhpParser\Node\Expr
    {
        return $this->firstExpr;
    }
    public function getSecondExpr() : \PhpParser\Node\Expr
    {
        return $this->secondExpr;
    }
}
