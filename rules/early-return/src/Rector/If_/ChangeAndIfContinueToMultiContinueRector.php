<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfContinueToMultiContinueRector\ChangeAndIfContinueToMultiContinueRectorTest
 */
final class ChangeAndIfContinueToMultiContinueRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes if && to early return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $newCar)
    {
        foreach ($cars as $car) {
            if ($car->hasWheels() && $car->hasFuel()) {
                continue;
            }

            $car->setWheel($newCar->wheel);
            $car->setFuel($newCar->fuel);
        }
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $newCar)
    {
        foreach ($cars as $car) {
            if (! $car->hasWheels()) {
                continue;
            }
            if (! $car->hasFuel()) {
                continue;
            }

            $car->setWheel($newCar->wheel);
            $car->setFuel($newCar->fuel);
        }
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
        if (! $this->ifManipulator->isIfWithOnly($node, Continue_::class)) {
            return null;
        }

        if (! $node->cond instanceof BooleanAnd) {
            return null;
        }

        return $this->processMultiIfContinue($node);
    }

    private function processMultiIfContinue(If_ $if): If_
    {
        $node = clone $if;
        /** @var Continue_ $continue */
        $continue = $if->stmts[0];
        $ifs = $this->createMultipleIfs($if->cond, $continue, []);
        foreach ($ifs as $key => $if) {
            if ($key === 0) {
                $this->mirrorComments($if, $node);
            }

            $this->addNodeBeforeNode($if, $node);
        }

        $this->removeNode($node);
        return $node;
    }

    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function createMultipleIfs(Expr $expr, Continue_ $continue, array $ifs): array
    {
        while ($expr instanceof BooleanAnd) {
            $ifs = array_merge($ifs, $this->collectLeftBooleanAndToIfs($expr, $continue, $ifs));
            $ifs[] = $this->ifManipulator->createIfNegation($expr->right, $continue);

            $expr = $expr->right;
        }

        return $ifs + [$this->ifManipulator->createIfNegation($expr, $continue)];
    }

    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function collectLeftBooleanAndToIfs(BooleanAnd $booleanAnd, Continue_ $continue, array $ifs): array
    {
        $left = $booleanAnd->left;
        if (! $left instanceof BooleanAnd) {
            return [$this->ifManipulator->createIfNegation($left, $continue)];
        }

        return $this->createMultipleIfs($left, $continue, $ifs);
    }
}
