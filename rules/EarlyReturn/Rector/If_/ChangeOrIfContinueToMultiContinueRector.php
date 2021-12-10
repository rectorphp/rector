<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector\ChangeOrIfContinueToMultiContinueRectorTest
 */
final class ChangeOrIfContinueToMultiContinueRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes if || to early return', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $newCar)
    {
        foreach ($cars as $car) {
            if ($car->hasWheels() || $car->hasFuel()) {
                continue;
            }

            $car->setWheel($newCar->wheel);
            $car->setFuel($newCar->fuel);
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $newCar)
    {
        foreach ($cars as $car) {
            if ($car->hasWheels()) {
                continue;
            }
            if ($car->hasFuel()) {
                continue;
            }

            $car->setWheel($newCar->wheel);
            $car->setFuel($newCar->fuel);
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     * @return null|If_[]
     */
    public function refactor(\PhpParser\Node $node) : ?array
    {
        if (!$this->ifManipulator->isIfWithOnly($node, \PhpParser\Node\Stmt\Continue_::class)) {
            return null;
        }
        if (!$node->cond instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return null;
        }
        return $this->processMultiIfContinue($node);
    }
    /**
     * @return null|If_[]
     */
    private function processMultiIfContinue(\PhpParser\Node\Stmt\If_ $if) : ?array
    {
        $node = clone $if;
        /** @var Continue_ $continue */
        $continue = $if->stmts[0];
        $ifs = $this->createMultipleIfs($if->cond, $continue, []);
        // ensure ifs not removed by other rules
        if ($ifs === []) {
            return null;
        }
        $this->mirrorComments($ifs[0], $node);
        return $ifs;
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function createMultipleIfs(\PhpParser\Node\Expr $expr, \PhpParser\Node\Stmt\Continue_ $continue, array $ifs) : array
    {
        while ($expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            $ifs = \array_merge($ifs, $this->collectLeftbooleanOrToIfs($expr, $continue, $ifs));
            $ifs[] = $this->ifManipulator->createIfExpr($expr->right, $continue);
            $expr = $expr->right;
        }
        return $ifs + [$this->ifManipulator->createIfExpr($expr, $continue)];
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function collectLeftbooleanOrToIfs(\PhpParser\Node\Expr\BinaryOp\BooleanOr $booleanOr, \PhpParser\Node\Stmt\Continue_ $continue, array $ifs) : array
    {
        $left = $booleanOr->left;
        if (!$left instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return [$this->ifManipulator->createIfExpr($left, $continue)];
        }
        return $this->createMultipleIfs($left, $continue, $ifs);
    }
}
