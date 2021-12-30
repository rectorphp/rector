<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr;
use Rector\Php80\Enum\MatchKind;
final class CondAndExpr
{
    /**
     * @var Expr[]|null
     * @readonly
     */
    private $condExprs;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    /**
     * @readonly
     * @var \Rector\Php80\Enum\MatchKind
     */
    private $matchKind;
    /**
     * @param Expr[]|null $condExprs
     */
    public function __construct($condExprs, \PhpParser\Node\Expr $expr, \Rector\Php80\Enum\MatchKind $matchKind)
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
     * @return mixed[]|null
     */
    public function getCondExprs()
    {
        // internally checked by PHPStan, cannot be empty array
        if ($this->condExprs === []) {
            return null;
        }
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
