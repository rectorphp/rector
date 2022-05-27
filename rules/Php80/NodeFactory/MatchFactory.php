<?php

declare (strict_types=1);
namespace Rector\Php80\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Match_;
use Rector\Php80\ValueObject\CondAndExpr;
final class MatchFactory
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\MatchArmsFactory
     */
    private $matchArmsFactory;
    public function __construct(\Rector\Php80\NodeFactory\MatchArmsFactory $matchArmsFactory)
    {
        $this->matchArmsFactory = $matchArmsFactory;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function createFromCondAndExprs(\PhpParser\Node\Expr $condExpr, array $condAndExprs) : \PhpParser\Node\Expr\Match_
    {
        $matchArms = $this->matchArmsFactory->createFromCondAndExprs($condAndExprs);
        return new \PhpParser\Node\Expr\Match_($condExpr, $matchArms);
    }
}
