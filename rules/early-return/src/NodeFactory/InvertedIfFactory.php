<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class InvertedIfFactory
{
    /**
     * @var array<class-string<Stmt>>
     */
    private const LOOP_TYPES = [Foreach_::class, For_::class, While_::class];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var ConditionInverter
     */
    private $conditionInverter;

    public function __construct(BetterNodeFinder $betterNodeFinder, ConditionInverter $conditionInverter)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->conditionInverter = $conditionInverter;
    }

    /**
     * @param Expr[] $conditions
     * @return If_[]
     */
    public function createFromConditions(If_ $if, array $conditions, Return_ $return): array
    {
        $ifs = [];
        $stmt = $this->isIfInLoop($if) && ! $this->getIfNextReturn($if)
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

    private function isIfInLoop(If_ $if): bool
    {
        $parentLoop = $this->betterNodeFinder->findParentTypes($if, self::LOOP_TYPES);
        return $parentLoop !== null;
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
