<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class InvertedIfFactory
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var ConditionInverter
     */
    private $conditionInverter;
    /**
     * @var ContextAnalyzer
     */
    private $contextAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\EarlyReturn\NodeTransformer\ConditionInverter $conditionInverter, \Rector\NodeNestingScope\ContextAnalyzer $contextAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->conditionInverter = $conditionInverter;
        $this->contextAnalyzer = $contextAnalyzer;
    }
    /**
     * @param Expr[] $conditions
     * @return If_[]
     */
    public function createFromConditions(\PhpParser\Node\Stmt\If_ $if, array $conditions, \PhpParser\Node\Stmt\Return_ $return) : array
    {
        $ifs = [];
        $stmt = $this->contextAnalyzer->isInLoop($if) && !$this->getIfNextReturn($if) ? [new \PhpParser\Node\Stmt\Continue_()] : [$return];
        $getNextReturnExpr = $this->getNextReturnExpr($if);
        if ($getNextReturnExpr instanceof \PhpParser\Node\Stmt\Return_) {
            $return->expr = $getNextReturnExpr->expr;
        }
        foreach ($conditions as $condition) {
            $invertedCondition = $this->conditionInverter->createInvertedCondition($condition);
            $if = new \PhpParser\Node\Stmt\If_($invertedCondition);
            $if->stmts = $stmt;
            $ifs[] = $if;
        }
        return $ifs;
    }
    private function getNextReturnExpr(\PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node
    {
        $closure = $this->betterNodeFinder->findParentType($if, \PhpParser\Node\Expr\Closure::class);
        if ($closure instanceof \PhpParser\Node\Expr\Closure) {
            return null;
        }
        return $this->betterNodeFinder->findFirstNext($if, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Stmt\Return_ && $node->expr instanceof \PhpParser\Node\Expr;
        });
    }
    private function getIfNextReturn(\PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Stmt\Return_
    {
        $nextNode = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        return $nextNode;
    }
}
