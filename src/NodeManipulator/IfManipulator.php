<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Rector\NodeNameResolver\NodeNameResolver;
final class IfManipulator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeTransformer\ConditionInverter
     */
    private $conditionInverter;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, \Rector\Core\NodeManipulator\StmtsManipulator $stmtsManipulator, ValueResolver $valueResolver, ConditionInverter $conditionInverter, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->valueResolver = $valueResolver;
        $this->conditionInverter = $conditionInverter;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * Matches:
     *
     * if (<$value> !== null) {
     *     return $value;
     * }
     */
    public function matchIfNotNullReturnValue(If_ $if) : ?Expr
    {
        if (\count($if->stmts) !== 1) {
            return null;
        }
        $insideIfNode = $if->stmts[0];
        if (!$insideIfNode instanceof Return_) {
            return null;
        }
        if (!$if->cond instanceof NotIdentical) {
            return null;
        }
        return $this->matchComparedAndReturnedNode($if->cond, $insideIfNode);
    }
    /**
     * Matches:
     *
     * if (<$value> === null) {
     *     return null;
     * }
     *
     * if (<$value> === 53;) {
     *     return 53;
     * }
     */
    public function matchIfValueReturnValue(If_ $if) : ?Expr
    {
        if (\count($if->stmts) !== 1) {
            return null;
        }
        $insideIfStmt = $if->stmts[0];
        if (!$insideIfStmt instanceof Return_) {
            return null;
        }
        if (!$if->cond instanceof Identical) {
            return null;
        }
        if ($this->nodeComparator->areNodesEqual($if->cond->left, $insideIfStmt->expr)) {
            return $if->cond->right;
        }
        if ($this->nodeComparator->areNodesEqual($if->cond->right, $insideIfStmt->expr)) {
            return $if->cond->left;
        }
        return null;
    }
    /**
     * @return mixed[]
     */
    public function collectNestedIfsWithOnlyReturn(If_ $if) : array
    {
        $ifs = [];
        $currentIf = $if;
        while ($this->isIfWithOnlyStmtIf($currentIf)) {
            $ifs[] = $currentIf;
            /** @var If_ $currentIf */
            $currentIf = $currentIf->stmts[0];
        }
        if ($ifs === []) {
            return [];
        }
        if (!$this->hasOnlyStmtOfType($currentIf, Return_::class)) {
            return [];
        }
        // last node is with the return value
        $ifs[] = $currentIf;
        return $ifs;
    }
    public function isIfAndElseWithSameVariableAssignAsLastStmts(If_ $if, Expr $desiredExpr) : bool
    {
        if ($if->else === null) {
            return \false;
        }
        if ((bool) $if->elseifs) {
            return \false;
        }
        $lastIfStmt = $this->stmtsManipulator->getUnwrappedLastStmt($if->stmts);
        if (!$lastIfStmt instanceof Assign) {
            return \false;
        }
        $lastElseStmt = $this->stmtsManipulator->getUnwrappedLastStmt($if->else->stmts);
        if (!$lastElseStmt instanceof Assign) {
            return \false;
        }
        if (!$lastIfStmt->var instanceof Variable) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($lastIfStmt->var, $lastElseStmt->var)) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($desiredExpr, $lastElseStmt->var);
    }
    /**
     * Matches:
     * if (<some_function>) {
     * } else {
     * }
     */
    public function isIfOrIfElseWithFunctionCondition(If_ $if, string $functionName) : bool
    {
        if ((bool) $if->elseifs) {
            return \false;
        }
        if (!$if->cond instanceof FuncCall) {
            return \false;
        }
        return $this->nodeNameResolver->isName($if->cond, $functionName);
    }
    /**
     * @return If_[]
     */
    public function collectNestedIfsWithNonBreaking(Foreach_ $foreach) : array
    {
        if (\count($foreach->stmts) !== 1) {
            return [];
        }
        $onlyForeachStmt = $foreach->stmts[0];
        if (!$onlyForeachStmt instanceof If_) {
            return [];
        }
        $ifs = [];
        $currentIf = $onlyForeachStmt;
        while ($this->isIfWithOnlyStmtIf($currentIf)) {
            $ifs[] = $currentIf;
            /** @var If_ $currentIf */
            $currentIf = $currentIf->stmts[0];
        }
        // IfManipulator is not build to handle elseif and else
        if (!$this->isIfWithoutElseAndElseIfs($currentIf)) {
            return [];
        }
        $betterNodeFinderFindInstanceOf = $this->betterNodeFinder->findInstanceOf($currentIf->stmts, Return_::class);
        if ($betterNodeFinderFindInstanceOf !== []) {
            return [];
        }
        /** @var Exit_[] $exits */
        $exits = $this->betterNodeFinder->findInstanceOf($currentIf->stmts, Exit_::class);
        if ($exits !== []) {
            return [];
        }
        // last node is with the expression
        $ifs[] = $currentIf;
        return $ifs;
    }
    /**
     * @param class-string<Node> $className
     */
    public function isIfWithOnly(Node $node, string $className) : bool
    {
        if (!$node instanceof If_) {
            return \false;
        }
        if (!$this->isIfWithoutElseAndElseIfs($node)) {
            return \false;
        }
        return $this->hasOnlyStmtOfType($node, $className);
    }
    public function isIfWithOnlyOneStmt(If_ $if) : bool
    {
        return \count($if->stmts) === 1;
    }
    public function isIfWithoutElseAndElseIfs(If_ $if) : bool
    {
        if ($if->else !== null) {
            return \false;
        }
        return !(bool) $if->elseifs;
    }
    public function createIfNegation(Expr $expr, Return_ $return) : If_
    {
        $expr = $this->conditionInverter->createInvertedCondition($expr);
        return $this->createIfStmt($expr, $return);
    }
    public function createIfStmt(Expr $expr, Stmt $stmt) : If_
    {
        return new If_($expr, ['stmts' => [$stmt]]);
    }
    private function matchComparedAndReturnedNode(NotIdentical $notIdentical, Return_ $return) : ?Expr
    {
        if ($this->nodeComparator->areNodesEqual($notIdentical->left, $return->expr) && $this->valueResolver->isNull($notIdentical->right)) {
            return $notIdentical->left;
        }
        if (!$this->nodeComparator->areNodesEqual($notIdentical->right, $return->expr)) {
            return null;
        }
        if ($this->valueResolver->isNull($notIdentical->left)) {
            return $notIdentical->right;
        }
        return null;
    }
    private function isIfWithOnlyStmtIf(If_ $if) : bool
    {
        if (!$this->isIfWithoutElseAndElseIfs($if)) {
            return \false;
        }
        return $this->hasOnlyStmtOfType($if, If_::class);
    }
    /**
     * @param class-string<Node> $desiredType
     */
    private function hasOnlyStmtOfType(If_ $if, string $desiredType) : bool
    {
        $stmts = $if->stmts;
        if (\count($stmts) !== 1) {
            return \false;
        }
        return \is_a($stmts[0], $desiredType);
    }
}
