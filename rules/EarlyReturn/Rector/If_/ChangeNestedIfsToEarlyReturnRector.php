<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector\ChangeNestedIfsToEarlyReturnRectorTest
 */
final class ChangeNestedIfsToEarlyReturnRector extends \Rector\Core\Rector\AbstractRector
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
        return [\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        foreach ($stmts as $key => $stmt) {
            $nextStmt = $stmts[$key + 1] ?? null;
            if (!$nextStmt instanceof \PhpParser\Node\Stmt\Return_) {
                continue;
            }
            if (!$stmt instanceof \PhpParser\Node\Stmt\If_) {
                continue;
            }
            $nestedIfsWithOnlyReturn = $this->ifManipulator->collectNestedIfsWithOnlyReturn($stmt);
            if ($nestedIfsWithOnlyReturn === []) {
                continue;
            }
            $node->stmts = $this->processNestedIfsWithOnlyReturn($nestedIfsWithOnlyReturn, $nextStmt);
            return $node;
        }
        return null;
    }
    /**
     * @param If_[] $nestedIfsWithOnlyReturn
     * @return Stmt[]
     */
    private function processNestedIfsWithOnlyReturn(array $nestedIfsWithOnlyReturn, \PhpParser\Node\Stmt\Return_ $nextReturn) : array
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
        $newStmts[] = $nextReturn;
        return $newStmts;
    }
    /**
     * @return Stmt[]
     */
    private function createStandaloneIfsWithReturn(\PhpParser\Node\Stmt\If_ $nestedIfWithOnlyReturn, \PhpParser\Node\Stmt\Return_ $return) : array
    {
        $invertedCondition = $this->conditionInverter->createInvertedCondition($nestedIfWithOnlyReturn->cond);
        // special case
        if ($invertedCondition instanceof \PhpParser\Node\Expr\BooleanNot && $invertedCondition->expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
            $booleanNotPartIf = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BooleanNot($invertedCondition->expr->left));
            $booleanNotPartIf->stmts = [clone $return];
            $secondBooleanNotPartIf = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BooleanNot($invertedCondition->expr->right));
            $secondBooleanNotPartIf->stmts = [clone $return];
            return [$booleanNotPartIf, $secondBooleanNotPartIf];
        }
        $nestedIfWithOnlyReturn->cond = $invertedCondition;
        $nestedIfWithOnlyReturn->stmts = [$return];
        return [$nestedIfWithOnlyReturn];
    }
}
