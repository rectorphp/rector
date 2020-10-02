<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Core\PhpParser\Node\Manipulator\StmtsManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\SOLID\NodeTransformer\ConditionInverter;

/**
 * @see \Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\ChangeAndIfToEarlyReturnRectorTest
 */
final class ChangeAndIfToEarlyReturnRector extends AbstractRector
{
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes if && to early return', [
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
        if ($ifReturn === null) {
            return null;
        }

        /** @var BooleanAnd $expr */
        $expr = $node->cond;
        $invertedLeftCondition = $this->conditionInverter->createInvertedCondition($expr->left);
        $invertedRightCondition = $this->conditionInverter->createInvertedCondition($expr->right);

        $firstIf = new If_($invertedLeftCondition);
        $firstIf->stmts = [new Return_()];
        $secondIf = new If_($invertedRightCondition);
        $secondIf->stmts = [new Return_()];

        $this->addNodeAfterNode($firstIf, $node);
        $this->addNodeAfterNode($secondIf, $node);
        $this->addNodeAfterNode($ifReturn, $node);
        $this->removeNode($node);

        $functionLikeReturn = $this->getFunctionLikeReturn($node);
        if ($functionLikeReturn !== null) {
            $this->removeNode($functionLikeReturn);
        }

        return null;
    }

    private function shouldSkip(If_ $if): bool
    {
        if (! $this->ifManipulator->isIfWithOnlyOneStmt($if)) {
            return true;
        }

        if (! $this->ifManipulator->isIfFirstLevelStmt($if)) {
            return true;
        }

        if (! $if->cond instanceof BooleanAnd) {
            return true;
        }

        if ($this->hasMoreThanTwoConditions($if)) {
            return true;
        }

        if (! $this->isFunctionLikeReturnsVoid($if)) {
            return true;
        }

        if ($if->else !== null) {
            return true;
        }

        if ($if->elseifs !== []) {
            return true;
        }

        return ! $this->isLastIfOrBeforeLastReturn($if);
    }

    private function getIfReturn(If_ $if): ?Stmt
    {
        $ifStmt = end($if->stmts);
        if ($ifStmt === false) {
            return null;
        }

        return $ifStmt;
    }

    private function getFunctionLikeReturn(If_ $if): ?Return_
    {
        /** @var FunctionLike|null $functionLike */
        $functionLike = $this->betterNodeFinder->findFirstParentInstanceOf($if, FunctionLike::class);
        if ($functionLike === null) {
            return null;
        }

        if ($functionLike->getStmts() === null) {
            return null;
        }

        $return = $this->stmtsManipulator->getUnwrappedLastStmt($functionLike->getStmts());
        if ($return === null) {
            return null;
        }

        if (! $return instanceof Return_) {
            return null;
        }

        return $return;
    }

    private function hasMoreThanTwoConditions(If_ $if): bool
    {
        $binaryOps = $this->betterNodeFinder->findInstanceOf($if->cond, BooleanAnd::class);
        return count($binaryOps) >= 2;
    }

    private function isFunctionLikeReturnsVoid(If_ $if): bool
    {
        $return = $this->getFunctionLikeReturn($if);
        if ($return === null) {
            return true;
        }

        return $return->expr === null;
    }

    private function isLastIfOrBeforeLastReturn(If_ $if): bool
    {
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
        if ($nextNode === null) {
            return true;
        }
        return $nextNode instanceof Return_;
    }
}
