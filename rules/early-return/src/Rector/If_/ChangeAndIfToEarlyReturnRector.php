<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\ChangeAndIfToEarlyReturnRectorTest
 */
final class ChangeAndIfToEarlyReturnRector extends AbstractRector
{
    /**
     * @var array<class-string<Stmt>>
     */
    public const LOOP_TYPES = [Foreach_::class, For_::class, While_::class];

    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var ConditionInverter
     */
    private $conditionInverter;

    public function __construct(ConditionInverter $conditionInverter, IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->conditionInverter = $conditionInverter;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes if && to early return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels && $car->hasFuel) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $car)
    {
        if (!$car->hasWheels) {
            return false;
        }

        if (!$car->hasFuel) {
            return false;
        }

        return true;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $ifNextReturn = $this->getIfNextReturn($node);
        if ($ifNextReturn instanceof Return_ && $this->isIfStmtExprUsedInNextReturn($node, $ifNextReturn)) {
            return null;
        }

        /** @var BooleanAnd $expr */
        $expr = $node->cond;
        $conditions = $this->nodeRepository->findBooleanAndConditions($expr);
        $ifNextReturnClone = $ifNextReturn instanceof Return_
            ? clone $ifNextReturn
            : new Return_();

        $isInLoop = $this->isIfInLoop($node);
        if (! $ifNextReturn instanceof Return_) {
            $this->addNodeAfterNode($node->stmts[0], $node);
            return $this->processReplaceIfs($node, $conditions, $ifNextReturnClone);
        }

        $this->removeNode($ifNextReturn);
        $ifNextReturn = $node->stmts[0];
        $this->addNodeAfterNode($ifNextReturn, $node);

        if (! $isInLoop) {
            return $this->processReplaceIfs($node, $conditions, $ifNextReturnClone);
        }

        if (! $ifNextReturn instanceof Expression) {
            return null;
        }

        if ($ifNextReturn->expr instanceof Expr) {
            $this->addNodeAfterNode(new Return_(), $node);
        }

        return $this->processReplaceIfs($node, $conditions, $ifNextReturnClone);
    }

    /**
     * @param Expr[] $conditions
     */
    private function processReplaceIfs(If_ $node, array $conditions, Return_ $ifNextReturnClone): If_
    {
        $ifs = $this->createInvertedIfNodesFromConditions($node, $conditions, $ifNextReturnClone);
        $this->mirrorComments($ifs[0], $node);

        foreach ($ifs as $if) {
            $this->addNodeBeforeNode($if, $node);
        }

        $this->removeNode($node);

        if (! $node->stmts[0] instanceof Return_ && $ifNextReturnClone->expr instanceof Expr) {
            $this->addNodeAfterNode($ifNextReturnClone, $node);
        }

        return $node;
    }

    private function shouldSkip(If_ $if): bool
    {
        if (! $this->ifManipulator->isIfWithOnlyOneStmt($if)) {
            return true;
        }

        if (! $if->cond instanceof BooleanAnd || ! $this->ifManipulator->isIfWithoutElseAndElseIfs($if)) {
            return true;
        }

        if ($this->isParentIfReturnsVoidOrParentIfHasNextNode($if)) {
            return true;
        }

        if ($this->isNestedIfInLoop($if)) {
            return true;
        }

        return ! $this->isLastIfOrBeforeLastReturn($if);
    }

    private function isIfStmtExprUsedInNextReturn(If_ $if, Return_ $return): bool
    {
        if (! $return->expr instanceof Expr) {
            return false;
        }

        $ifExprs = $this->betterNodeFinder->findInstanceOf($if->stmts, Expr::class);
        foreach ($ifExprs as $expr) {
            $isExprFoundInReturn = (bool) $this->betterNodeFinder->findFirst($return->expr, function (Node $node) use (
                $expr
            ): bool {
                return $this->areNodesEqual($node, $expr);
            });
            if ($isExprFoundInReturn) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Expr[] $conditions
     * @return If_[]
     */
    private function createInvertedIfNodesFromConditions(If_ $if, array $conditions, Return_ $return): array
    {
        $ifs = [];
        $stmt = $this->isIfInLoop($if) && ! $this->getIfNextReturn($if)
            ? [new Continue_()]
            : [$return];

        $getNextReturnExpr = $this->getNextReturnExpr($if);
        if ($getNextReturnExpr instanceof Return_) {
            $return->expr = $getNextReturnExpr->expr;
        }

        foreach ($conditions as $key => $condition) {
            $invertedCondition = $this->conditionInverter->createInvertedCondition($condition);
            $if = new If_($invertedCondition);
            $if->stmts = $stmt;
            $ifs[] = $if;
        }

        return $ifs;
    }

    private function getIfNextReturn(If_ $if): ?Return_
    {
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return null;
        }

        return $nextNode;
    }

    private function getNextReturnExpr(If_ $if): ?Return_
    {
        $hasClosureParent = (bool) $this->betterNodeFinder->findParentType($if, Closure::class);
        if ($hasClosureParent) {
            return null;
        }

        return $this->betterNodeFinder->findFirstNext($if, function (Node $node): bool {
            return $node instanceof Return_ && $node->expr instanceof Expr;
        });
    }

    private function isIfInLoop(If_ $if): bool
    {
        $parentLoop = $this->betterNodeFinder->findParentTypes($if, self::LOOP_TYPES);

        return $parentLoop !== null;
    }

    private function isParentIfReturnsVoidOrParentIfHasNextNode(If_ $if): bool
    {
        $parentNode = $if->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof If_) {
            return false;
        }

        $nextParent = $parentNode->getAttribute(AttributeKey::NEXT_NODE);
        return $nextParent instanceof Node;
    }

    private function isNestedIfInLoop(If_ $if): bool
    {
        if (! $this->isIfInLoop($if)) {
            return false;
        }
        return (bool) $this->betterNodeFinder->findParentTypes($if, [If_::class, Else_::class, ElseIf_::class]);
    }

    private function isLastIfOrBeforeLastReturn(If_ $if): bool
    {
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
        if ($nextNode instanceof Node) {
            return $nextNode instanceof Return_;
        }

        $parent = $if->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof If_) {
            return $this->isLastIfOrBeforeLastReturn($parent);
        }

        return true;
    }
}
