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
     * @var MatchKind::*
     * @readonly
     */
    private $matchKind;
    /**
     * @param Expr[]|null $condExprs
     * @param MatchKind::* $matchKind
     */
    public function __construct($condExprs, \PhpParser\Node\Expr $expr, string $matchKind)
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
    /**
     * @return MatchKind::*
     */
    public function getMatchKind() : string
    {
        return $this->matchKind;
    }
    /**
     * @param MatchKind::* $matchKind
     */
    public function equalsMatchKind(string $matchKind) : bool
    {
        return $this->matchKind === $matchKind;
    }
}
