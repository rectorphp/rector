<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\Core\NodeManipulator\IfManipulator;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;

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
            if ($car->hasWheels && $car->hasFuel) {
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
            if (! $car->hasWheels) {
                continue;
            }

            if (! $car->hasFuel) {
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
        if (! $this->ifManipulator->isIfWithOnlyOneStmt($node)) {
            return null;
        }

        $stmt = $node->stmts[0];
        if (! $stmt instanceof Continue_) {
            return null;
        }

        if (! $node->cond instanceof BooleanAnd) {
            return null;
        }

        return $node;
    }
}
