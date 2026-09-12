<?php

declare (strict_types=1);
namespace Rector\Php84\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Php84\NodeAnalyzer\ForeachKeyUsedInConditionalAnalyzer;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\Value\ValueResolver;
/**
 * Shared logic for ForeachToArrayFindRector and ForeachToArrayFindKeyRector.
 */
final class ForeachToArrayFindFactory
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
    public function createArrayFindAssign(Node $node, string $functionName, bool $compareKey): ?Node
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
            if (!$this->valueResolver->isNull($prevAssign->expr)) {
                continue;
            }
            if (!$prevAssign->var instanceof Variable) {
                continue;
            }
            $assignedVariable = $prevAssign->var;
            if (!$this->isValidForeachStructure($foreach, $assignedVariable, $compareKey)) {
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
            $arrowFunction = new ArrowFunction(['params' => $params, 'expr' => $condition]);
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
    private function isValidForeachStructure(Foreach_ $foreach, Variable $assignedVariable, bool $compareKey): bool
    {
        if (count($foreach->stmts) !== 1) {
            return \false;
        }
        $comparedExpr = $compareKey ? $foreach->keyVar : $foreach->valueVar;
        if (!$comparedExpr instanceof Expr) {
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
        if (!$this->nodeComparator->areNodesEqual($assignment->expr, $comparedExpr)) {
            return \false;
        }
        if (!$foreach->valueVar instanceof Variable) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getNativeType($foreach->expr);
        return $type->isArray()->yes();
    }
}
