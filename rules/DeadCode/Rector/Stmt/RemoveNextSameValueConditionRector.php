<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\If_;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Stmt\RemoveNextSameValueConditionRector\RemoveNextSameValueConditionRectorTest
 */
final class RemoveNextSameValueConditionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SideEffectNodeDetector $sideEffectNodeDetector;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(SideEffectNodeDetector $sideEffectNodeDetector, BetterNodeFinder $betterNodeFinder)
    {
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove already checked if condition repeated in the very next stmt', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function __construct(array $items)
    {
        $count = 100;
        if ($items === []) {
            $count = 0;
        }

        if ($items === []) {
            return $count;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function __construct(array $items)
    {
        $count = 100;
        if ($items === []) {
            $count = 0;
            return $count;
        }
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
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
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
            // first condition must be without side effect
            if ($this->sideEffectNodeDetector->detect($stmt->cond)) {
                continue;
            }
            if ($this->isCondVariableUsedInIfBody($stmt)) {
                continue;
            }
            $nextStmt = $node->stmts[$key + 1] ?? null;
            if (!$nextStmt instanceof If_) {
                continue;
            }
            if (!$this->nodeComparator->areNodesEqual($stmt->cond, $nextStmt->cond)) {
                continue;
            }
            $stmt->stmts = array_merge($stmt->stmts, $nextStmt->stmts);
            // remove next node
            unset($node->stmts[$key + 1]);
            return $node;
        }
        return null;
    }
    private function isCondVariableUsedInIfBody(If_ $if): bool
    {
        $condVariables = $this->betterNodeFinder->findInstancesOf($if->cond, [Variable::class]);
        foreach ($condVariables as $condVariable) {
            $condVariableName = $this->getName($condVariable);
            if ($condVariableName === null) {
                continue;
            }
            if ($this->betterNodeFinder->findVariableOfName($if->stmts, $condVariableName) instanceof Variable) {
                return \true;
            }
        }
        return \false;
    }
}
