<?php

declare (strict_types=1);
namespace Rector\Php84\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\Php84\NodeAnalyzer\ForeachKeyUsedInConditionalAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php84\Rector\Foreach_\ForeachToArrayAllRector\ForeachToArrayAllRectorTest
 */
final class ForeachToArrayAllRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private StmtsManipulator $stmtsManipulator;
    /**
     * @readonly
     */
    private ForeachKeyUsedInConditionalAnalyzer $foreachKeyUsedInConditionalAnalyzer;
    public function __construct(ValueResolver $valueResolver, StmtsManipulator $stmtsManipulator, ForeachKeyUsedInConditionalAnalyzer $foreachKeyUsedInConditionalAnalyzer)
    {
        $this->valueResolver = $valueResolver;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->foreachKeyUsedInConditionalAnalyzer = $foreachKeyUsedInConditionalAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace foreach with boolean assignment and break OR foreach with early return with array_all', [new CodeSample(<<<'CODE_SAMPLE'
$found = true;
foreach ($animals as $animal) {
    if (!str_starts_with($animal, 'c')) {
        $found = false;
        break;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$found = array_all($animals, fn($animal) => str_starts_with($animal, 'c'));
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
foreach ($animals as $animal) {
    if (!str_starts_with($animal, 'c')) {
        return false;
    }
}
return true;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return array_all($animals, fn($animal) => str_starts_with($animal, 'c'));
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
        return $this->refactorBooleanAssignmentPattern($node) ?? $this->refactorEarlyReturnPattern($node);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ARRAY_ALL;
    }
    private function refactorBooleanAssignmentPattern(StmtsAwareInterface $stmtsAware): ?Node
    {
        foreach ($stmtsAware->stmts as $key => $stmt) {
            if (!$stmt instanceof Foreach_) {
                continue;
            }
            $prevStmt = $stmtsAware->stmts[$key - 1] ?? null;
            if (!$prevStmt instanceof Expression) {
                continue;
            }
            if (!$prevStmt->expr instanceof Assign) {
                continue;
            }
            $foreach = $stmt;
            $prevAssign = $prevStmt->expr;
            if (!$this->valueResolver->isTrue($prevAssign->expr)) {
                continue;
            }
            if (!$prevAssign->var instanceof Variable) {
                continue;
            }
            $assignedVariable = $prevAssign->var;
            if (!$this->isValidBooleanAssignmentForeachStructure($foreach, $assignedVariable)) {
                continue;
            }
            if ($this->stmtsManipulator->isVariableUsedInNextStmt($stmtsAware, $key + 1, (string) $this->getName($foreach->valueVar))) {
                continue;
            }
            /** @var If_ $firstNodeInsideForeach */
            $firstNodeInsideForeach = $foreach->stmts[0];
            $condition = $firstNodeInsideForeach->cond;
            $valueParam = $foreach->valueVar;
            if (!$valueParam instanceof Variable) {
                continue;
            }
            $params = [new Param($valueParam)];
            if ($foreach->keyVar instanceof Variable && $this->foreachKeyUsedInConditionalAnalyzer->isUsed($foreach->keyVar, $condition)) {
                $params[] = new Param(new Variable((string) $this->getName($foreach->keyVar)));
            }
            $negatedCondition = $condition instanceof BooleanNot ? $condition->expr : new BooleanNot($condition);
            $arrowFunction = new ArrowFunction(['params' => $params, 'expr' => $negatedCondition]);
            $funcCall = $this->nodeFactory->createFuncCall('array_all', [$foreach->expr, $arrowFunction]);
            $newAssign = new Assign($assignedVariable, $funcCall);
            $newExpression = new Expression($newAssign);
            unset($stmtsAware->stmts[$key - 1]);
            $stmtsAware->stmts[$key] = $newExpression;
            $stmtsAware->stmts = array_values($stmtsAware->stmts);
            return $stmtsAware;
        }
        return null;
    }
    private function refactorEarlyReturnPattern(StmtsAwareInterface $stmtsAware): ?Node
    {
        foreach ($stmtsAware->stmts as $key => $stmt) {
            if (!$stmt instanceof Foreach_) {
                continue;
            }
            $foreach = $stmt;
            $nextStmt = $stmtsAware->stmts[$key + 1] ?? null;
            if (!$nextStmt instanceof Return_) {
                continue;
            }
            if (!$nextStmt->expr instanceof Expr) {
                continue;
            }
            if (!$this->valueResolver->isTrue($nextStmt->expr)) {
                continue;
            }
            if (!$this->isValidEarlyReturnForeachStructure($foreach)) {
                continue;
            }
            /** @var If_ $firstNodeInsideForeach */
            $firstNodeInsideForeach = $foreach->stmts[0];
            $condition = $firstNodeInsideForeach->cond;
            $params = [];
            if ($foreach->valueVar instanceof Variable) {
                $params[] = new Param($foreach->valueVar);
            }
            if ($foreach->keyVar instanceof Variable && $this->foreachKeyUsedInConditionalAnalyzer->isUsed($foreach->keyVar, $condition)) {
                $params[] = new Param(new Variable((string) $this->getName($foreach->keyVar)));
            }
            $negatedCondition = $condition instanceof BooleanNot ? $condition->expr : new BooleanNot($condition);
            $arrowFunction = new ArrowFunction(['params' => $params, 'expr' => $negatedCondition]);
            $funcCall = $this->nodeFactory->createFuncCall('array_all', [$foreach->expr, $arrowFunction]);
            $stmtsAware->stmts[$key] = new Return_($funcCall);
            unset($stmtsAware->stmts[$key + 1]);
            $stmtsAware->stmts = array_values($stmtsAware->stmts);
            return $stmtsAware;
        }
        return null;
    }
    private function isValidEarlyReturnForeachStructure(Foreach_ $foreach): bool
    {
        if (count($foreach->stmts) !== 1) {
            return \false;
        }
        if (!$foreach->stmts[0] instanceof If_) {
            return \false;
        }
        $ifStmt = $foreach->stmts[0];
        if (count($ifStmt->stmts) !== 1) {
            return \false;
        }
        if (!$ifStmt->stmts[0] instanceof Return_) {
            return \false;
        }
        $returnStmt = $ifStmt->stmts[0];
        if (!$returnStmt->expr instanceof Expr) {
            return \false;
        }
        if (!$this->valueResolver->isFalse($returnStmt->expr)) {
            return \false;
        }
        if (!$foreach->valueVar instanceof Variable) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getNativeType($foreach->expr);
        return $type->isArray()->yes();
    }
    private function isValidBooleanAssignmentForeachStructure(Foreach_ $foreach, Variable $assignedVariable): bool
    {
        if (count($foreach->stmts) !== 1) {
            return \false;
        }
        $firstStmt = $foreach->stmts[0];
        if (!$firstStmt instanceof If_ || count($firstStmt->stmts) !== 2) {
            return \false;
        }
        $assignmentStmt = $firstStmt->stmts[0];
        $breakStmt = $firstStmt->stmts[1];
        if (!$assignmentStmt instanceof Expression || !$assignmentStmt->expr instanceof Assign || !$breakStmt instanceof Break_) {
            return \false;
        }
        $assignment = $assignmentStmt->expr;
        if (!$this->nodeComparator->areNodesEqual($assignment->var, $assignedVariable)) {
            return \false;
        }
        if (!$this->valueResolver->isFalse($assignment->expr)) {
            return \false;
        }
        if (!$foreach->valueVar instanceof Variable) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getNativeType($foreach->expr);
        return $type->isArray()->yes();
    }
}
