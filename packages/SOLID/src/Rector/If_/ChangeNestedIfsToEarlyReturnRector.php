<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\SOLID\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\ChangeNestedIfsToEarlyReturnRectorTest
 */
final class ChangeNestedIfsToEarlyReturnRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(IfManipulator $ifManipulator, BinaryOpManipulator $binaryOpManipulator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->binaryOpManipulator = $binaryOpManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change nested ifs to early return', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        if ($value === 5) {
            if ($value2 === 10) {
                return 'yes';
            }
        }

        return 'no';
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        if ($value !== 5) {
            return 'no';
        }

        if ($value2 === 10) {
            return 'yes';
        }

        return 'no';
    }
}
PHP

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
        // A. next node is return
        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return null;
        }

        $nestedIfsWithOnlyReturn = $this->ifManipulator->collectNestedIfsWithOnlyReturn($node);
        if ($nestedIfsWithOnlyReturn === []) {
            return null;
        }

        $this->processNestedIfsWIthOnlyReturn($node, $nestedIfsWithOnlyReturn, $nextNode);
        $this->removeNode($node);

        return null;
    }

    /**
     * @param If_[] $nestedIfsWithOnlyReturn
     */
    private function processNestedIfsWIthOnlyReturn(If_ $if, array $nestedIfsWithOnlyReturn, Return_ $nextReturn): void
    {
        // add nested if openly after this
        $nestedIfsWithOnlyReturnCount = count($nestedIfsWithOnlyReturn);

        /** @var int $key */
        foreach ($nestedIfsWithOnlyReturn as $key => $nestedIfWithOnlyReturn) {
            // last item → the return node
            if ($nestedIfsWithOnlyReturnCount === $key + 1) {
                $this->addNodeAfterNode($nestedIfWithOnlyReturn, $if);
            } else {
                $this->addStandaloneIfsWithReturn($nestedIfWithOnlyReturn, $if, $nextReturn);
            }
        }
    }

    private function createInvertedCondition(Expr $expr): Expr
    {
        // inverse condition
        if ($expr instanceof BinaryOp) {
            $inversedCondition = $this->binaryOpManipulator->invertCondition($expr);
            if ($inversedCondition === null) {
                return new BooleanNot($expr);
            }

            return $inversedCondition;
        }

        return new BooleanNot($expr);
    }

    private function addStandaloneIfsWithReturn(If_ $nestedIfWithOnlyReturn, If_ $if, Return_ $return): void
    {
        $return = clone $return;

        $invertedCondition = $this->createInvertedCondition($nestedIfWithOnlyReturn->cond);

        // special case
        if ($invertedCondition instanceof BooleanNot) {
            if ($invertedCondition->expr instanceof BooleanAnd) {
                $booleanNotPartIf = new If_(new BooleanNot($invertedCondition->expr->left));
                $booleanNotPartIf->stmts = [clone $return];
                $this->addNodeAfterNode($booleanNotPartIf, $if);

                $booleanNotPartIf = new If_(new BooleanNot($invertedCondition->expr->right));
                $booleanNotPartIf->stmts = [clone $return];
                $this->addNodeAfterNode($booleanNotPartIf, $if);

                return;
            }
        }

        $nestedIfWithOnlyReturn->cond = $invertedCondition;
        $nestedIfWithOnlyReturn->stmts = [clone $return];

        $this->addNodeAfterNode($nestedIfWithOnlyReturn, $if);
    }
}
