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
final class ChangeNestedForeachIfsToEarlyContinueRector extends AbstractRector
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
    public function __construct(ConditionInverter $conditionInverter, IfManipulator $ifManipulator)
    {
        $this->conditionInverter = $conditionInverter;
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change nested ifs to foreach with continue', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node) : ?Node
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
    private function processNestedIfsWithNonBreaking(Foreach_ $foreach, array $nestedIfsWithOnlyReturn) : Foreach_
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
    private function addInvertedIfStmtWithContinue(If_ $nestedIfWithOnlyReturn, Foreach_ $foreach) : void
    {
        $invertedCondExpr = $this->conditionInverter->createInvertedCondition($nestedIfWithOnlyReturn->cond);
        // special case
        if ($invertedCondExpr instanceof BooleanNot && $invertedCondExpr->expr instanceof BooleanAnd) {
            $leftExpr = $this->negateOrDeNegate($invertedCondExpr->expr->left);
            $foreach->stmts[] = $this->createIfContinue($leftExpr);
            $rightExpr = $this->negateOrDeNegate($invertedCondExpr->expr->right);
            $foreach->stmts[] = $this->createIfContinue($rightExpr);
            return;
        }
        // should skip for weak inversion
        if ($this->isBooleanOrWithWeakComparison($nestedIfWithOnlyReturn->cond)) {
            $foreach->stmts[] = $nestedIfWithOnlyReturn;
            return;
        }
        $nestedIfWithOnlyReturn->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $nestedIfWithOnlyReturn->cond = $invertedCondExpr;
        $nestedIfWithOnlyReturn->stmts = [new Continue_()];
        $foreach->stmts[] = $nestedIfWithOnlyReturn;
    }
    /**
     * Matches:
     * $a == 1 || $b == 1
     *
     * Skips:
     * $a === 1 || $b === 2
     */
    private function isBooleanOrWithWeakComparison(Expr $expr) : bool
    {
        if (!$expr instanceof BooleanOr) {
            return \false;
        }
        if ($expr->left instanceof Equal) {
            return \true;
        }
        if ($expr->left instanceof NotEqual) {
            return \true;
        }
        if ($expr->right instanceof Equal) {
            return \true;
        }
        return $expr->right instanceof NotEqual;
    }
    private function negateOrDeNegate(Expr $expr) : Expr
    {
        if ($expr instanceof BooleanNot) {
            return $expr->expr;
        }
        return new BooleanNot($expr);
    }
    private function createIfContinue(Expr $expr) : If_
    {
        return new If_($expr, ['stmts' => [new Continue_()]]);
    }
}
