<?php

declare (strict_types=1);
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
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector\ChangeNestedIfsToEarlyReturnRectorTest
 */
final class ChangeNestedIfsToEarlyReturnRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\EarlyReturn\NodeTransformer\ConditionInverter
     */
    private $conditionInverter;
    /**
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(\Rector\EarlyReturn\NodeTransformer\ConditionInverter $conditionInverter, \Rector\Core\NodeManipulator\IfManipulator $ifManipulator)
    {
        $this->conditionInverter = $conditionInverter;
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change nested ifs to early return', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        // A. next node is return
        $nextNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node\Stmt\Return_) {
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
    private function processNestedIfsWithOnlyReturn(\PhpParser\Node\Stmt\If_ $if, array $nestedIfsWithOnlyReturn, \PhpParser\Node\Stmt\Return_ $nextReturn) : void
    {
        // add nested if openly after this
        $nestedIfsWithOnlyReturnCount = \count($nestedIfsWithOnlyReturn);
        /** @var int $key */
        foreach ($nestedIfsWithOnlyReturn as $key => $nestedIfWithOnlyReturn) {
            // last item → the return node
            if ($nestedIfsWithOnlyReturnCount === $key + 1) {
                $this->nodesToAddCollector->addNodeAfterNode($nestedIfWithOnlyReturn, $if);
            } else {
                $this->addStandaloneIfsWithReturn($nestedIfWithOnlyReturn, $if, $nextReturn);
            }
        }
    }
    private function addStandaloneIfsWithReturn(\PhpParser\Node\Stmt\If_ $nestedIfWithOnlyReturn, \PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Stmt\Return_ $return) : void
    {
        $return = clone $return;
        $invertedCondition = $this->conditionInverter->createInvertedCondition($nestedIfWithOnlyReturn->cond);
        // special case
        if ($invertedCondition instanceof \PhpParser\Node\Expr\BooleanNot && $invertedCondition->expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
            $booleanNotPartIf = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BooleanNot($invertedCondition->expr->left));
            $booleanNotPartIf->stmts = [clone $return];
            $this->nodesToAddCollector->addNodeAfterNode($booleanNotPartIf, $if);
            $booleanNotPartIf = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BooleanNot($invertedCondition->expr->right));
            $booleanNotPartIf->stmts = [clone $return];
            $this->nodesToAddCollector->addNodeAfterNode($booleanNotPartIf, $if);
            return;
        }
        $nestedIfWithOnlyReturn->cond = $invertedCondition;
        $nestedIfWithOnlyReturn->stmts = [clone $return];
        $this->nodesToAddCollector->addNodeAfterNode($nestedIfWithOnlyReturn, $if);
    }
}
