<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Stmt\RemoveConditionExactReturnRector\RemoveConditionExactReturnRectorTest
 */
final class RemoveConditionExactReturnRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SideEffectNodeDetector $sideEffectNodeDetector;
    public function __construct(SideEffectNodeDetector $sideEffectNodeDetector)
    {
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove if with condition and return with same expr, followed by compared expr return', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function __construct(array $items)
    {
        if ($items === []) {
            return [];
        }

        return $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function __construct(array $items)
    {
        return $items;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            $soleIfReturn = $this->matchSoleIfReturn($stmt);
            if (!$soleIfReturn instanceof Return_) {
                continue;
            }
            if (!$stmt->cond instanceof Identical && !$stmt->cond instanceof Equal) {
                continue;
            }
            $identicalOrEqual = $stmt->cond;
            if ($this->sideEffectNodeDetector->detect($identicalOrEqual->right)) {
                continue;
            }
            if (!$this->nodeComparator->areNodesEqual($identicalOrEqual->right, $soleIfReturn->expr)) {
                continue;
            }
            $comparedVariable = $identicalOrEqual->left;
            // next if must be return of the same var
            $nextStmt = $node->stmts[$key + 1] ?? null;
            if (!$nextStmt instanceof Return_) {
                continue;
            }
            if (!$nextStmt->expr instanceof Expr) {
                continue;
            }
            if (!$this->nodeComparator->areNodesEqual($nextStmt->expr, $comparedVariable)) {
                continue;
            }
            // remove next if
            unset($node->stmts[$key + 1]);
            // replace if with return
            $node->stmts[$key] = $nextStmt;
            return $node;
        }
        return null;
    }
    private function matchSoleIfReturn(If_ $if): ?Return_
    {
        if (count($if->stmts) !== 1) {
            return null;
        }
        $soleIfStmt = $if->stmts[0];
        if (!$soleIfStmt instanceof Return_) {
            return null;
        }
        return $soleIfStmt;
    }
}
