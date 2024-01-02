<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Rector\NodeManipulator\IfManipulator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector\ChangeNestedIfsToEarlyReturnRectorTest
 */
final class ChangeNestedIfsToEarlyReturnRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeTransformer\ConditionInverter
     */
    private $conditionInverter;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(ConditionInverter $conditionInverter, IfManipulator $ifManipulator)
    {
        $this->conditionInverter = $conditionInverter;
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change nested ifs to early return', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?StmtsAwareInterface
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            $nextStmt = $node->stmts[$key + 1] ?? null;
            if (!$nextStmt instanceof Return_) {
                return null;
            }
            $nestedIfsWithOnlyReturn = $this->ifManipulator->collectNestedIfsWithOnlyReturn($stmt);
            if ($nestedIfsWithOnlyReturn === []) {
                continue;
            }
            $newStmts = $this->processNestedIfsWithOnlyReturn($nestedIfsWithOnlyReturn, $nextStmt);
            // replace nested ifs with many separate ifs
            \array_splice($node->stmts, $key, 1, $newStmts);
            return $node;
        }
        return null;
    }
    /**
     * @param If_[] $nestedIfsWithOnlyReturn
     * @return If_[]
     */
    private function processNestedIfsWithOnlyReturn(array $nestedIfsWithOnlyReturn, Return_ $nextReturn) : array
    {
        // add nested if openly after this
        $nestedIfsWithOnlyReturnCount = \count($nestedIfsWithOnlyReturn);
        $newStmts = [];
        /** @var int $key */
        foreach ($nestedIfsWithOnlyReturn as $key => $nestedIfWithOnlyReturn) {
            // last item → the return node
            if ($nestedIfsWithOnlyReturnCount === $key + 1) {
                $newStmts[] = $nestedIfWithOnlyReturn;
            } else {
                $standaloneIfs = $this->createStandaloneIfsWithReturn($nestedIfWithOnlyReturn, $nextReturn);
                $newStmts = \array_merge($newStmts, $standaloneIfs);
            }
        }
        // $newStmts[] = $nextReturn;
        return $newStmts;
    }
    /**
     * @return If_[]
     */
    private function createStandaloneIfsWithReturn(If_ $onlyReturnIf, Return_ $return) : array
    {
        $invertedCondExpr = $this->conditionInverter->createInvertedCondition($onlyReturnIf->cond);
        // special case
        if ($invertedCondExpr instanceof BooleanNot && $invertedCondExpr->expr instanceof BooleanAnd) {
            $booleanNotPartIf = new If_(new BooleanNot($invertedCondExpr->expr->left));
            $booleanNotPartIf->stmts = [clone $return];
            $secondBooleanNotPartIf = new If_(new BooleanNot($invertedCondExpr->expr->right));
            $secondBooleanNotPartIf->stmts = [clone $return];
            return [$booleanNotPartIf, $secondBooleanNotPartIf];
        }
        $onlyReturnIf->cond = $invertedCondExpr;
        $onlyReturnIf->stmts = [$return];
        return [$onlyReturnIf];
    }
}
