<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeAnalyzer\VariableAnalyzer;
use Rector\Php\ReservedKeywordAnalyzer;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveOverriddenAssignBeforeIfElseRector\RemoveOverriddenAssignBeforeIfElseRectorTest
 */
final class RemoveOverriddenAssignBeforeIfElseRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReservedKeywordAnalyzer $reservedKeywordAnalyzer;
    /**
     * @readonly
     */
    private SideEffectNodeDetector $sideEffectNodeDetector;
    /**
     * @readonly
     */
    private VariableAnalyzer $variableAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(ReservedKeywordAnalyzer $reservedKeywordAnalyzer, SideEffectNodeDetector $sideEffectNodeDetector, VariableAnalyzer $variableAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->variableAnalyzer = $variableAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove assign before if/else that overrides the variable in both branches', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value)
    {
        $result = [];
        if ($value) {
            $result = [1, 2, 3];
        } else {
            $result = [4, 5, 6];
        }

        return $result;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value)
    {
        if ($value) {
            $result = [1, 2, 3];
        } else {
            $result = [4, 5, 6];
        }

        return $result;
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
     * @return StmtsAware|null
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            $variableName = $this->matchOverridableAssignVariableName($stmt);
            if ($variableName === null) {
                continue;
            }
            $nextStmt = $node->stmts[$key + 1] ?? null;
            if (!$nextStmt instanceof If_) {
                continue;
            }
            if (!$nextStmt->else instanceof Else_ || $nextStmt->elseifs !== []) {
                continue;
            }
            // the initial value is still read when the condition uses it
            if ($this->isVariableUsedInNode($nextStmt->cond, $variableName)) {
                continue;
            }
            // compact()/get_defined_vars()/dynamic variables can read the value by name
            if ($this->shouldSkipIf($nextStmt)) {
                continue;
            }
            if (!$this->isOverriddenFirstInBranch($nextStmt->stmts, $variableName)) {
                continue;
            }
            if (!$this->isOverriddenFirstInBranch($nextStmt->else->stmts, $variableName)) {
                continue;
            }
            unset($node->stmts[$key]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function matchOverridableAssignVariableName(Stmt $stmt): ?string
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$assign->var instanceof Variable) {
            return null;
        }
        // removing the assign must not drop a side effect
        if ($this->sideEffectNodeDetector->detect($assign->expr)) {
            return null;
        }
        if ($this->variableAnalyzer->isStaticOrGlobal($assign->var)) {
            return null;
        }
        if ($this->variableAnalyzer->isUsedByReference($assign->var)) {
            return null;
        }
        $variableName = $this->getName($assign->var);
        if (!is_string($variableName)) {
            return null;
        }
        if ($this->reservedKeywordAnalyzer->isNativeVariable($variableName)) {
            return null;
        }
        return $variableName;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isOverriddenFirstInBranch(array $stmts, string $variableName): bool
    {
        foreach ($stmts as $stmt) {
            if (!$this->isVariableUsedInNode($stmt, $variableName)) {
                continue;
            }
            // first statement touching the variable must fully override it
            if (!$stmt instanceof Expression || !$stmt->expr instanceof Assign) {
                return \false;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof Variable || !$this->isName($assign->var, $variableName)) {
                return \false;
            }
            // e.g. $value = $value + 1 still reads the previous value
            return !$this->isVariableUsedInNode($assign->expr, $variableName);
        }
        return \false;
    }
    private function shouldSkipIf(If_ $if): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($if, function (Node $subNode): bool {
            if ($subNode instanceof FuncCall) {
                return $this->isNames($subNode, ['compact', 'get_defined_vars', 'extract']);
            }
            // dynamic variable access like $$name can read the value by name
            return $subNode instanceof Variable && !is_string($subNode->name);
        });
    }
    private function isVariableUsedInNode(Node $node, string $variableName): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($node, fn(Node $subNode): bool => $subNode instanceof Variable && $this->isName($subNode, $variableName));
    }
}
