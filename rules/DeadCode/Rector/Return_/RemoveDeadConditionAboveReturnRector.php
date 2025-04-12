<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\RemoveDeadConditionAboveReturnRectorTest
 */
final class RemoveDeadConditionAboveReturnRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SideEffectNodeDetector $sideEffectNodeDetector;
    public function __construct(SideEffectNodeDetector $sideEffectNodeDetector)
    {
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove dead condition above return', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        if (1 === 1) {
            return 'yes';
        }

        return 'yes';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        return 'yes';
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
        foreach ((array) $node->stmts as $key => $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            $previousNode = $node->stmts[$key - 1] ?? null;
            if (!$this->isBareIf($previousNode)) {
                continue;
            }
            /** @var If_ $previousNode */
            if ($this->sideEffectNodeDetector->detect($previousNode->cond)) {
                continue;
            }
            $countStmt = \count($previousNode->stmts);
            if ($countStmt === 0) {
                unset($node->stmts[$key - 1]);
                return $node;
            }
            if ($countStmt > 1) {
                return null;
            }
            $previousFirstStmt = $previousNode->stmts[0];
            if (!$previousFirstStmt instanceof Return_) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($previousFirstStmt, $stmt)) {
                return null;
            }
            unset($node->stmts[$key - 1]);
            return $node;
        }
        return null;
    }
    private function isBareIf(?Stmt $stmt) : bool
    {
        if (!$stmt instanceof If_) {
            return \false;
        }
        if ($stmt->elseifs !== []) {
            return \false;
        }
        return !$stmt->else instanceof Else_;
    }
}
