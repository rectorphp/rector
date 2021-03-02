<?php

declare(strict_types=1);

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

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        ConditionInverter $conditionInverter,
        ContextAnalyzer $contextAnalyzer
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->conditionInverter = $conditionInverter;
        $this->contextAnalyzer = $contextAnalyzer;
    }

    /**
     * @param Expr[] $conditions
     * @return If_[]
     */
    public function createFromConditions(If_ $if, array $conditions, Return_ $return): array
    {
        $ifs = [];
        $stmt = $this->contextAnalyzer->isInLoop($if) && ! $this->getIfNextReturn($if)
            ? [new Continue_()]
            : [$return];

        $getNextReturnExpr = $this->getNextReturnExpr($if);
        if ($getNextReturnExpr instanceof Return_) {
            $return->expr = $getNextReturnExpr->expr;
        }

        foreach ($conditions as $condition) {
            $invertedCondition = $this->conditionInverter->createInvertedCondition($condition);
            $if = new If_($invertedCondition);
            $if->stmts = $stmt;
            $ifs[] = $if;
        }

        return $ifs;
    }

    private function getNextReturnExpr(If_ $if): ?Node
    {
        /** @var Closure|null $closure */
        $closure = $if->getAttribute(AttributeKey::CLOSURE_NODE);
        if ($closure instanceof Closure) {
            return null;
        }

        return $this->betterNodeFinder->findFirstNext($if, function (Node $node): bool {
            return $node instanceof Return_ && $node->expr instanceof Expr;
        });
    }

    private function getIfNextReturn(If_ $if): ?Return_
    {
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return null;
        }

        return $nextNode;
    }
}
