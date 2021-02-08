<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\ChangeNestedIfsToEarlyReturnRectorTest
 */
final class ChangeNestedIfsToEarlyReturnRector extends AbstractRector
{
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
        return new RuleDefinition('Change nested ifs to early return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
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
        // A. next node is return
        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return null;
        }

        $nestedIfsWithOnlyReturn = $this->ifManipulator->collectNestedIfsWithOnlyReturn($node);
        if ($nestedIfsWithOnlyReturn === []) {
            return null;
        }

        $this->processNestedIfsWithOnlyReturn($node, $nestedIfsWithOnlyReturn, $nextNode);
        $this->removeNode($node);

        return null;
    }

    /**
     * @param If_[] $nestedIfsWithOnlyReturn
     */
    private function processNestedIfsWithOnlyReturn(If_ $if, array $nestedIfsWithOnlyReturn, Return_ $nextReturn): void
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

    private function addStandaloneIfsWithReturn(If_ $nestedIfWithOnlyReturn, If_ $if, Return_ $return): void
    {
        $return = clone $return;

        $invertedCondition = $this->conditionInverter->createInvertedCondition($nestedIfWithOnlyReturn->cond);

        // special case
        if ($invertedCondition instanceof BooleanNot && $invertedCondition->expr instanceof BooleanAnd) {
            $booleanNotPartIf = new If_(new BooleanNot($invertedCondition->expr->left));
            $booleanNotPartIf->stmts = [clone $return];
            $this->addNodeAfterNode($booleanNotPartIf, $if);

            $booleanNotPartIf = new If_(new BooleanNot($invertedCondition->expr->right));
            $booleanNotPartIf->stmts = [clone $return];
            $this->addNodeAfterNode($booleanNotPartIf, $if);
            return;
        }

        $nestedIfWithOnlyReturn->cond = $invertedCondition;
        $nestedIfWithOnlyReturn->stmts = [clone $return];

        $this->addNodeAfterNode($nestedIfWithOnlyReturn, $if);
    }
}
