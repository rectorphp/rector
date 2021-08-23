<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr;
use Rector\Php80\Enum\MatchKind;
final class CondAndExpr
{
    /**
     * @var \PhpParser\Node\Expr[]
     */
    private $condExprs;
    /**
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    /**
     * @var \Rector\Php80\Enum\MatchKind
     */
    private $matchKind;
    /**
     * @param Expr[] $condExprs
     */
    public function __construct(array $condExprs, \PhpParser\Node\Expr $expr, \Rector\Php80\Enum\MatchKind $matchKind)
    {
        $this->condExprs = $condExprs;
        $this->expr = $expr;
        $this->matchKind = $matchKind;
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
    public function getMatchKind() : \Rector\Php80\Enum\MatchKind
    {
        return $this->matchKind;
    }
    public function equalsMatchKind(\Rector\Php80\Enum\MatchKind $matchKind) : bool
    {
        return $this->matchKind->equals($matchKind);
    }
}
