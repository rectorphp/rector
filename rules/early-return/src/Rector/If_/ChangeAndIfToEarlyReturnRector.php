<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\NodeManipulator\StmtsManipulator;
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

    /**
     * @var StmtsManipulator
     */
    private $stmtsManipulator;

    public function __construct(
        ConditionInverter $conditionInverter,
        IfManipulator $ifManipulator,
        StmtsManipulator $stmtsManipulator
    ) {
        $this->ifManipulator = $ifManipulator;
        $this->conditionInverter = $conditionInverter;
        $this->stmtsManipulator = $stmtsManipulator;
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
     * @return string[]
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

        $ifReturn = $this->getIfReturn($node);
        if (! $ifReturn instanceof Stmt) {
            return null;
        }

        /** @var BooleanAnd $expr */
        $expr = $node->cond;

        $ifNextReturn = $this->getIfNextReturn($node);

        $conditions   = $this->getBooleanAndConditions($expr);

        $ifNextReturnClone = $ifNextReturn instanceof Return_
            ? clone $ifNextReturn
            : new Return_();

        $isInLoop = $this->isIfInLoop($node);
        if ($ifNextReturn instanceof Return_) {
            $this->removeNode($ifNextReturn);
            $ifNextReturn = $node->stmts[0];
            $this->addNodeAfterNode($ifNextReturn, $node);
            if ($isInLoop) {
                $this->addNodeAfterNode(new Return_(), $node);
            }
        } else {
            $this->addNodeAfterNode($node->stmts[0], $node);
        }

        $ifs          = $this->createInvertedIfNodesFromConditions($node, $conditions, $ifNextReturnClone);

        foreach ($ifs as $key => $if) {
            if ($key === 0) {
                $this->mirrorComments($if, $node);
            }

            $this->addNodeBeforeNode($if, $node);
        }

        $this->removeNode($node);
        return $node;
    }

    private function shouldSkip(If_ $if): bool
    {
        if (! $this->ifManipulator->isIfWithOnlyOneStmt($if)) {
            return true;
        }

        if (! $if->cond instanceof BooleanAnd) {
            return true;
        }

        if ($if->else !== null) {
            return true;
        }

        if ($if->elseifs !== []) {
            return true;
        }

        if ($this->isParentIfReturnsVoid($if)) {
            return true;
        }

        if ($this->isNestedIfInLoop($if)) {
            return true;
        }

        return ! $this->isLastIfOrBeforeLastReturn($if);
    }

    private function getIfReturn(If_ $if): ?Stmt
    {
        return end($if->stmts) ?: null;
    }

    /**
     * @return Expr[]
     */
    private function getBooleanAndConditions(BooleanAnd $booleanAnd): array
    {
        $ifs = [];
        while (property_exists($booleanAnd, 'left')) {
            $ifs[]      = $booleanAnd->right;
            $booleanAnd = $booleanAnd->left;

            if (! $booleanAnd instanceof BooleanAnd) {
                $ifs[] = $booleanAnd;
                break;
            }
        }

        krsort($ifs);
        return $ifs;
    }

    /**
     * @param Expr[] $conditions
     * @return If_[]
     */
    private function createInvertedIfNodesFromConditions(If_ $if, array $conditions, Return_ $return): array
    {
        $ifs = [];
        $isInLoop = $this->isIfInLoop($if);
        $getIfNextReturn = $this->getIfNextReturn($if);

        foreach ($conditions as $condition) {
            $invertedCondition = $this->conditionInverter->createInvertedCondition($condition);
            $if        = new If_($invertedCondition);
            $if->stmts = [$isInLoop && $getIfNextReturn === null ? new Continue_() : $return];

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

    private function isIfInLoop(If_ $if): bool
    {
        $parentLoop = $this->betterNodeFinder->findParentTypes($if, self::LOOP_TYPES);

        return $parentLoop !== null;
    }

    private function isIfReturnsVoid(If_ $if): bool
    {
        $lastStmt = $this->stmtsManipulator->getUnwrappedLastStmt($if->stmts);
        if (! $lastStmt instanceof Return_) {
            return false;
        }
        return $lastStmt->expr === null;
    }

    private function isParentIfReturnsVoid(If_ $if): bool
    {
        $parentNode = $if->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof If_) {
            return false;
        }

        return $this->isIfReturnsVoid($parentNode);
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
