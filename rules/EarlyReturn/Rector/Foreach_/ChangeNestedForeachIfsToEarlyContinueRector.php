<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\ChangeNestedForeachIfsToEarlyContinueRectorTest
 */
final class ChangeNestedForeachIfsToEarlyContinueRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeTransformer\ConditionInverter
     */
    private $conditionInverter;
    /**
     * @readonly
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change nested ifs to foreach with continue', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $nestedIfsWithOnlyNonReturn = $this->ifManipulator->collectNestedIfsWithNonBreaking($node);
        if (\count($nestedIfsWithOnlyNonReturn) < 2) {
            return null;
        }
        return $this->processNestedIfsWithNonBreaking($node, $nestedIfsWithOnlyNonReturn);
    }
    /**
     * @param If_[] $nestedIfsWithOnlyReturn
     */
    private function processNestedIfsWithNonBreaking(\PhpParser\Node\Stmt\Foreach_ $foreach, array $nestedIfsWithOnlyReturn) : \PhpParser\Node\Stmt\Foreach_
    {
        // add nested if openly after this
        $nestedIfsWithOnlyReturnCount = \count($nestedIfsWithOnlyReturn);
        // clear
        $foreach->stmts = [];
        foreach ($nestedIfsWithOnlyReturn as $key => $nestedIfWithOnlyReturn) {
            // last item → the return node
            if ($nestedIfsWithOnlyReturnCount === $key + 1) {
                $finalReturn = clone $nestedIfWithOnlyReturn;
                $this->addInvertedIfStmtWithContinue($nestedIfWithOnlyReturn, $foreach);
                // should skip for weak inversion
                if ($this->isBooleanOrWithWeakComparison($nestedIfWithOnlyReturn->cond)) {
                    continue;
                }
                $foreach->stmts = \array_merge($foreach->stmts, $finalReturn->stmts);
            } else {
                $this->addInvertedIfStmtWithContinue($nestedIfWithOnlyReturn, $foreach);
            }
        }
        return $foreach;
    }
    private function addInvertedIfStmtWithContinue(\PhpParser\Node\Stmt\If_ $nestedIfWithOnlyReturn, \PhpParser\Node\Stmt\Foreach_ $foreach) : void
    {
        $invertedCondition = $this->conditionInverter->createInvertedCondition($nestedIfWithOnlyReturn->cond);
        // special case
        if ($invertedCondition instanceof \PhpParser\Node\Expr\BooleanNot && $invertedCondition->expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
            $leftExpr = $this->negateOrDeNegate($invertedCondition->expr->left);
            $if = new \PhpParser\Node\Stmt\If_($leftExpr);
            $if->stmts[] = new \PhpParser\Node\Stmt\Continue_();
            $foreach->stmts[] = $if;
            $rightExpr = $this->negateOrDeNegate($invertedCondition->expr->right);
            $if = new \PhpParser\Node\Stmt\If_($rightExpr);
            $if->stmts[] = new \PhpParser\Node\Stmt\Continue_();
            $foreach->stmts[] = $if;
            return;
        }
        // should skip for weak inversion
        if ($this->isBooleanOrWithWeakComparison($nestedIfWithOnlyReturn->cond)) {
            $foreach->stmts[] = $nestedIfWithOnlyReturn;
            return;
        }
        $nestedIfWithOnlyReturn->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        $nestedIfWithOnlyReturn->cond = $invertedCondition;
        $nestedIfWithOnlyReturn->stmts = [new \PhpParser\Node\Stmt\Continue_()];
        $foreach->stmts[] = $nestedIfWithOnlyReturn;
    }
    /**
     * Matches:
     * $a == 1 || $b == 1
     *
     * Skips:
     * $a === 1 || $b === 2
     */
    private function isBooleanOrWithWeakComparison(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return \false;
        }
        if ($expr->left instanceof \PhpParser\Node\Expr\BinaryOp\Equal) {
            return \true;
        }
        if ($expr->left instanceof \PhpParser\Node\Expr\BinaryOp\NotEqual) {
            return \true;
        }
        if ($expr->right instanceof \PhpParser\Node\Expr\BinaryOp\Equal) {
            return \true;
        }
        return $expr->right instanceof \PhpParser\Node\Expr\BinaryOp\NotEqual;
    }
    private function negateOrDeNegate(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr
    {
        if ($expr instanceof \PhpParser\Node\Expr\BooleanNot) {
            return $expr->expr;
        }
        return new \PhpParser\Node\Expr\BooleanNot($expr);
    }
}
