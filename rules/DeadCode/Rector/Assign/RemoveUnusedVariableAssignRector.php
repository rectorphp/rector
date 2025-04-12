<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeAnalyzer\VariableAnalyzer;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\Php\ReservedKeywordAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
 */
final class RemoveUnusedVariableAssignRector extends AbstractRector
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
    /**
     * @readonly
     */
    private StmtsManipulator $stmtsManipulator;
    public function __construct(ReservedKeywordAnalyzer $reservedKeywordAnalyzer, SideEffectNodeDetector $sideEffectNodeDetector, VariableAnalyzer $variableAnalyzer, BetterNodeFinder $betterNodeFinder, StmtsManipulator $stmtsManipulator)
    {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->variableAnalyzer = $variableAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->stmtsManipulator = $stmtsManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused assigns to variables', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     * @return null|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    public function refactor(Node $node)
    {
        $stmts = $node->stmts;
        if ($stmts === null || $stmts === []) {
            return null;
        }
        // we cannot be sure here
        if ($this->shouldSkip($stmts)) {
            return null;
        }
        $assignedVariableNamesByStmtPosition = $this->resolvedAssignedVariablesByStmtPosition($stmts);
        $hasChanged = \false;
        foreach ($assignedVariableNamesByStmtPosition as $stmtPosition => $variableName) {
            if ($this->stmtsManipulator->isVariableUsedInNextStmt($node, $stmtPosition + 1, $variableName)) {
                continue;
            }
            /** @var Expression<Assign> $currentStmt */
            $currentStmt = $stmts[$stmtPosition];
            /** @var Assign $assign */
            $assign = $currentStmt->expr;
            if ($this->hasCallLikeInAssignExpr($assign)) {
                // clean safely
                $cleanAssignedExpr = $this->cleanCastedExpr($assign->expr);
                $newExpression = new Expression($cleanAssignedExpr);
                $this->mirrorComments($newExpression, $currentStmt);
                $node->stmts[$stmtPosition] = $newExpression;
            } else {
                unset($node->stmts[$stmtPosition]);
            }
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function cleanCastedExpr(Expr $expr) : Expr
    {
        if (!$expr instanceof Cast) {
            return $expr;
        }
        return $this->cleanCastedExpr($expr->expr);
    }
    private function hasCallLikeInAssignExpr(Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($expr, fn(Node $subNode): bool => $this->sideEffectNodeDetector->detectCallExpr($subNode));
    }
    /**
     * @param Stmt[] $stmts
     */
    private function shouldSkip(array $stmts) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $node) : bool {
            if ($node instanceof Include_) {
                return \true;
            }
            if (!$node instanceof FuncCall) {
                return \false;
            }
            return $this->isName($node, 'compact');
        });
    }
    /**
     * @param array<int, Stmt> $stmts
     * @return array<int, string>
     */
    private function resolvedAssignedVariablesByStmtPosition(array $stmts) : array
    {
        $assignedVariableNamesByStmtPosition = [];
        $refVariableNames = [];
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if ($stmt->expr instanceof AssignRef && $stmt->expr->var instanceof Variable) {
                $refVariableNames[] = (string) $this->getName($stmt->expr->var);
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof Variable) {
                continue;
            }
            $variableName = $this->getName($assign->var);
            if (!\is_string($variableName)) {
                continue;
            }
            if ($this->reservedKeywordAnalyzer->isNativeVariable($variableName)) {
                continue;
            }
            if ($this->shouldSkipVariable($assign->var, $variableName, $refVariableNames)) {
                continue;
            }
            $assignedVariableNamesByStmtPosition[$key] = $variableName;
        }
        return $assignedVariableNamesByStmtPosition;
    }
    /**
     * @param string[] $refVariableNames
     */
    private function shouldSkipVariable(Variable $variable, string $variableName, array $refVariableNames) : bool
    {
        if ($this->variableAnalyzer->isStaticOrGlobal($variable)) {
            return \true;
        }
        if ($this->variableAnalyzer->isUsedByReference($variable)) {
            return \true;
        }
        return \in_array($variableName, $refVariableNames, \true);
    }
}
