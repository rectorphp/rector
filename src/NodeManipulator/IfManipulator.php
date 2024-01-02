<?php

declare (strict_types=1);
namespace Rector\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Value\ValueResolver;
final class IfManipulator
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, \Rector\NodeManipulator\StmtsManipulator $stmtsManipulator, ValueResolver $valueResolver, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->valueResolver = $valueResolver;
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
     * @return If_[]
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
        // last if is with the return value
        $ifs[] = $currentIf;
        return $ifs;
    }
    public function isIfAndElseWithSameVariableAssignAsLastStmts(If_ $if, Expr $desiredExpr) : bool
    {
        if (!$if->else instanceof Else_) {
            return \false;
        }
        if ((bool) $if->elseifs) {
            return \false;
        }
        $lastIfNode = $this->stmtsManipulator->getUnwrappedLastStmt($if->stmts);
        if (!$lastIfNode instanceof Assign) {
            return \false;
        }
        $lastElseNode = $this->stmtsManipulator->getUnwrappedLastStmt($if->else->stmts);
        if (!$lastElseNode instanceof Assign) {
            return \false;
        }
        if (!$lastIfNode->var instanceof Variable) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($lastIfNode->var, $lastElseNode->var)) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($desiredExpr, $lastElseNode->var);
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
        $return = $this->betterNodeFinder->findFirstInstanceOf($currentIf->stmts, Return_::class);
        if ($return instanceof Return_) {
            return [];
        }
        $exit = $this->betterNodeFinder->findFirstInstanceOf($currentIf->stmts, Exit_::class);
        if ($exit instanceof Exit_) {
            return [];
        }
        // last if is with the expression
        $ifs[] = $currentIf;
        return $ifs;
    }
    /**
     * @param class-string<Stmt> $stmtClass
     */
    public function isIfWithOnly(If_ $if, string $stmtClass) : bool
    {
        if (!$this->isIfWithoutElseAndElseIfs($if)) {
            return \false;
        }
        return $this->hasOnlyStmtOfType($if, $stmtClass);
    }
    public function isIfWithOnlyOneStmt(If_ $if) : bool
    {
        return \count($if->stmts) === 1;
    }
    public function isIfWithoutElseAndElseIfs(If_ $if) : bool
    {
        if ($if->else instanceof Else_) {
            return \false;
        }
        return $if->elseifs === [];
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
     * @param class-string<Stmt> $stmtClass
     */
    private function hasOnlyStmtOfType(If_ $if, string $stmtClass) : bool
    {
        $stmts = $if->stmts;
        if (\count($stmts) !== 1) {
            return \false;
        }
        return $stmts[0] instanceof $stmtClass;
    }
}
