<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php80\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Match_;
use RectorPrefix20220606\Rector\Php80\ValueObject\CondAndExpr;
final class MatchFactory
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\MatchArmsFactory
     */
    private $matchArmsFactory;
    public function __construct(MatchArmsFactory $matchArmsFactory)
    {
        $this->matchArmsFactory = $matchArmsFactory;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function createFromCondAndExprs(Expr $condExpr, array $condAndExprs) : Match_
    {
        $matchArms = $this->matchArmsFactory->createFromCondAndExprs($condAndExprs);
        return new Match_($condExpr, $matchArms);
    }
}
