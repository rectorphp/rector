<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\SOLID\NodeTransformer\ConditionInverter;

/**
 * @see \Rector\SOLID\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\ChangeNestedForeachIfsToEarlyContinueRectorTest
 */
final class ChangeNestedForeachIfsToEarlyContinueRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var ConditionInverter
     */
    private $conditionInverter;

    public function __construct(IfManipulator $ifManipulator, ConditionInverter $conditionInverter)
    {
        $this->ifManipulator = $ifManipulator;
        $this->conditionInverter = $conditionInverter;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change nested ifs to foreach with continue', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $items = [];

        foreach ($values as $value) {
            if ($value === 5) {
                if ($value2 === 10) {
                    $items[] = 'maybe';
                }
            }
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $items = [];

        foreach ($values as $value) {
            if ($value !== 5) {
                continue;
            }
            if ($value2 !== 10) {
                continue;
            }

            $items[] = 'maybe';
        }
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
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $nestedIfsWithOnlyNonReturn = $this->ifManipulator->collectNestedIfsWithNonBreaking($node);
        if (count($nestedIfsWithOnlyNonReturn) < 2) {
            return null;
        }

        return $this->processNestedIfsWithNonBreaking($node, $nestedIfsWithOnlyNonReturn);
    }

    /**
     * @param If_[] $nestedIfsWithOnlyReturn
     */
    private function processNestedIfsWithNonBreaking(Foreach_ $foreach, array $nestedIfsWithOnlyReturn): Foreach_
    {
        // add nested if openly after this
        $nestedIfsWithOnlyReturnCount = count($nestedIfsWithOnlyReturn);

        // clear
        $foreach->stmts = [];

        foreach ($nestedIfsWithOnlyReturn as $key => $nestedIfWithOnlyReturn) {
            // last item → the return node
            if ($nestedIfsWithOnlyReturnCount === $key + 1) {
                $finalReturn = clone $nestedIfWithOnlyReturn;

                $this->addInvertedIfStmtWithContinue($nestedIfWithOnlyReturn, $foreach);

                $foreach->stmts = array_merge($foreach->stmts, $finalReturn->stmts);
            } else {
                $this->addInvertedIfStmtWithContinue($nestedIfWithOnlyReturn, $foreach);
            }
        }

        return $foreach;
    }

    private function addInvertedIfStmtWithContinue(If_ $nestedIfWithOnlyReturn, Foreach_ $foreach): void
    {
        $invertedCondition = $this->conditionInverter->createInvertedCondition($nestedIfWithOnlyReturn->cond);

        // special case
        if ($invertedCondition instanceof BooleanNot && $invertedCondition->expr instanceof BooleanAnd) {
            $booleanNotPartIf = new If_(new BooleanNot($invertedCondition->expr->left));
            $foreach->stmts[] = $booleanNotPartIf;

            $booleanNotPartIf = new If_(new BooleanNot($invertedCondition->expr->right));
            $foreach->stmts[] = $booleanNotPartIf;

            return;
        }

        $nestedIfWithOnlyReturn->setAttribute(AttributeKey::ORIGINAL_NODE, null);

        $nestedIfWithOnlyReturn->cond = $invertedCondition;
        $nestedIfWithOnlyReturn->stmts = [new Continue_()];

        $foreach->stmts[] = $nestedIfWithOnlyReturn;
    }
}
