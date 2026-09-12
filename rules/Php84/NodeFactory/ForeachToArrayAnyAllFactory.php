<?php

declare (strict_types=1);
namespace Rector\Php84\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Php84\NodeAnalyzer\ForeachKeyUsedInConditionalAnalyzer;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\Value\ValueResolver;
/**
 * Shared logic for ForeachToArrayAnyRector and ForeachToArrayAllRector.
 */
final class ForeachToArrayAnyAllFactory
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
    /**
     * @readonly
     */
    private NodeComparator $nodeComparator;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(ValueResolver $valueResolver, StmtsManipulator $stmtsManipulator, ForeachKeyUsedInConditionalAnalyzer $foreachKeyUsedInConditionalAnalyzer, NodeComparator $nodeComparator, NodeTypeResolver $nodeTypeResolver, NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->valueResolver = $valueResolver;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->foreachKeyUsedInConditionalAnalyzer = $foreachKeyUsedInConditionalAnalyzer;
        $this->nodeComparator = $nodeComparator;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactorToArrayAnyAll(Node $node, string $functionName, bool $negateCondition, bool $initialBool, bool $rejectElseBranches): ?Node
    {
        return $this->refactorBooleanAssignmentPattern($node, $functionName, $negateCondition, $initialBool, $rejectElseBranches) ?? $this->refactorEarlyReturnPattern($node, $functionName, $negateCondition, $initialBool, $rejectElseBranches);
    }
    /**
     * @param StmtsAware $node
     */
    private function refactorBooleanAssignmentPattern(Node $node, string $functionName, bool $negateCondition, bool $initialBool, bool $rejectElseBranches): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Foreach_) {
                continue;
            }
            $prevStmt = $node->stmts[$key - 1] ?? null;
            if (!$prevStmt instanceof Expression) {
                continue;
            }
            if (!$prevStmt->expr instanceof Assign) {
                continue;
            }
            $foreach = $stmt;
            $prevAssign = $prevStmt->expr;
            if (!$this->isExpectedBool($prevAssign->expr, $initialBool)) {
                continue;
            }
            if (!$prevAssign->var instanceof Variable) {
                continue;
            }
            $assignedVariable = $prevAssign->var;
            if (!$this->isValidBooleanAssignmentForeachStructure($foreach, $assignedVariable, $initialBool, $rejectElseBranches)) {
                continue;
            }
            if ($this->stmtsManipulator->isVariableUsedInNextStmt($node, $key + 1, (string) $this->nodeNameResolver->getName($foreach->valueVar))) {
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
                $params[] = new Param(new Variable((string) $this->nodeNameResolver->getName($foreach->keyVar)));
            }
            $arrowFunction = new ArrowFunction(['params' => $params, 'expr' => $this->applyNegation($condition, $negateCondition)]);
            $funcCall = $this->nodeFactory->createFuncCall($functionName, [$foreach->expr, $arrowFunction]);
            $newAssign = new Assign($assignedVariable, $funcCall);
            $newExpression = new Expression($newAssign);
            unset($node->stmts[$key - 1]);
            $node->stmts[$key] = $newExpression;
            $node->stmts = array_values($node->stmts);
            return $node;
        }
        return null;
    }
    /**
     * @param StmtsAware $node
     */
    private function refactorEarlyReturnPattern(Node $node, string $functionName, bool $negateCondition, bool $initialBool, bool $rejectElseBranches): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Foreach_) {
                continue;
            }
            $foreach = $stmt;
            $nextStmt = $node->stmts[$key + 1] ?? null;
            if (!$nextStmt instanceof Return_) {
                continue;
            }
            if (!$nextStmt->expr instanceof Expr) {
                continue;
            }
            if (!$this->isExpectedBool($nextStmt->expr, $initialBool)) {
                continue;
            }
            if (!$this->isValidEarlyReturnForeachStructure($foreach, $initialBool, $rejectElseBranches)) {
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
                $params[] = new Param(new Variable((string) $this->nodeNameResolver->getName($foreach->keyVar)));
            }
            $arrowFunction = new ArrowFunction(['params' => $params, 'expr' => $this->applyNegation($condition, $negateCondition)]);
            $funcCall = $this->nodeFactory->createFuncCall($functionName, [$foreach->expr, $arrowFunction]);
            $node->stmts[$key] = new Return_($funcCall);
            unset($node->stmts[$key + 1]);
            $node->stmts = array_values($node->stmts);
            return $node;
        }
        return null;
    }
    private function isValidBooleanAssignmentForeachStructure(Foreach_ $foreach, Variable $assignedVariable, bool $initialBool, bool $rejectElseBranches): bool
    {
        if (count($foreach->stmts) !== 1) {
            return \false;
        }
        $firstStmt = $foreach->stmts[0];
        if (!$firstStmt instanceof If_ || count($firstStmt->stmts) !== 2) {
            return \false;
        }
        if ($rejectElseBranches && ($firstStmt->elseifs !== [] || $firstStmt->else instanceof Else_)) {
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
        if (!$this->isExpectedBool($assignment->expr, !$initialBool)) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getNativeType($foreach->expr);
        return $type->isArray()->yes();
    }
    private function isValidEarlyReturnForeachStructure(Foreach_ $foreach, bool $initialBool, bool $rejectElseBranches): bool
    {
        if (count($foreach->stmts) !== 1) {
            return \false;
        }
        if (!$foreach->stmts[0] instanceof If_) {
            return \false;
        }
        $ifStmt = $foreach->stmts[0];
        if ($rejectElseBranches && ($ifStmt->elseifs !== [] || $ifStmt->else instanceof Else_)) {
            return \false;
        }
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
        if (!$this->isExpectedBool($returnStmt->expr, !$initialBool)) {
            return \false;
        }
        if (!$foreach->valueVar instanceof Variable) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getNativeType($foreach->expr);
        return $type->isArray()->yes();
    }
    private function isExpectedBool(Expr $expr, bool $expected): bool
    {
        if ($expected) {
            return $this->valueResolver->isTrue($expr);
        }
        return $this->valueResolver->isFalse($expr);
    }
    private function applyNegation(Expr $expr, bool $negate): Expr
    {
        if (!$negate) {
            return $expr;
        }
        return $expr instanceof BooleanNot ? $expr->expr : new BooleanNot($expr);
    }
}
