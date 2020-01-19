<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class IfManipulator
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ConstFetchManipulator
     */
    private $constFetchManipulator;

    /**
     * @var StmtsManipulator
     */
    private $stmtsManipulator;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ConstFetchManipulator $constFetchManipulator,
        StmtsManipulator $stmtsManipulator,
        NameResolver $nameResolver
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->constFetchManipulator = $constFetchManipulator;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->nameResolver = $nameResolver;
    }

    /**
     * Matches:
     *
     * if (<$value> !== null) {
     *     return $value;
     * }
     */
    public function matchIfNotNullReturnValue(If_ $ifNode): ?Expr
    {
        if (count($ifNode->stmts) !== 1) {
            return null;
        }

        $insideIfNode = $ifNode->stmts[0];
        if (! $insideIfNode instanceof Return_) {
            return null;
        }

        /** @var Return_ $returnNode */
        $returnNode = $insideIfNode;
        if (! $ifNode->cond instanceof NotIdentical) {
            return null;
        }

        return $this->matchComparedAndReturnedNode($ifNode->cond, $returnNode);
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
    public function matchIfValueReturnValue(If_ $ifNode): ?Expr
    {
        if (count($ifNode->stmts) !== 1) {
            return null;
        }

        $insideIfNode = $ifNode->stmts[0];
        if (! $insideIfNode instanceof Return_) {
            return null;
        }

        /** @var Return_ $returnNode */
        $returnNode = $insideIfNode;

        if (! $ifNode->cond instanceof Identical) {
            return null;
        }

        if ($this->betterStandardPrinter->areNodesEqual($ifNode->cond->left, $returnNode->expr)) {
            return $ifNode->cond->right;
        }

        if ($this->betterStandardPrinter->areNodesEqual($ifNode->cond->right, $returnNode->expr)) {
            return $ifNode->cond->left;
        }

        return null;
    }

    public function isIfWithOnlyStmtIf(If_ $if): bool
    {
        if (! $this->isIfWithoutElseAndElseIfs($if)) {
            return false;
        }

        return $this->hasOnlyStmtOfType($if, If_::class);
    }

    public function hasOnlyStmtOfType(If_ $if, string $desiredType): bool
    {
        if (count($if->stmts) !== 1) {
            return false;
        }

        return is_a($if->stmts[0], $desiredType);
    }

    /**
     * @return If_[]
     */
    public function collectNestedIfsWithOnlyReturn(If_ $if): array
    {
        $ifs = [];

        $currentIf = $if;
        while ($this->isIfWithOnlyStmtIf($currentIf)) {
            $ifs[] = $currentIf;

            $currentIf = $currentIf->stmts[0];
        }

        if ($ifs === []) {
            return [];
        }

        if (! $this->hasOnlyStmtOfType($currentIf, Return_::class)) {
            return [];
        }

        // last node is with the return value
        $ifs[] = $currentIf;

        return $ifs;
    }

    public function isIfWithElse(If_ $if): bool
    {
        if ($if->else === null) {
            return false;
        }

        return ! (bool) $if->elseifs;
    }

    public function isIfAndElseWithSameVariableAssignAsLastStmts(If_ $if, Expr $desiredExpr): bool
    {
        if (! $this->isIfWithElse($if)) {
            return false;
        }

        $lastIfStmt = $this->stmtsManipulator->getUnwrappedLastStmt($if->stmts);
        if (! $lastIfStmt instanceof Assign) {
            return false;
        }

        $lastElseStmt = $this->stmtsManipulator->getUnwrappedLastStmt($if->else->stmts);
        if (! $lastElseStmt instanceof Assign) {
            return false;
        }

        if (! $lastIfStmt->var instanceof Variable) {
            return false;
        }

        if (! $this->betterStandardPrinter->areNodesEqual($lastIfStmt->var, $lastElseStmt->var)) {
            return false;
        }
        return $this->betterStandardPrinter->areNodesEqual($desiredExpr, $lastElseStmt->var);
    }

    /**
     * Matches:
     * if (<some_function>) {
     * } else {
     * }
     */
    public function isIfOrIfElseWithFunctionCondition(If_ $if, string $functionName): bool
    {
        if ((bool) $if->elseifs) {
            return false;
        }

        if (! $if->cond instanceof FuncCall) {
            return false;
        }
        return $this->nameResolver->isName($if->cond, $functionName);
    }

    private function matchComparedAndReturnedNode(NotIdentical $notIdentical, Return_ $returnNode): ?Expr
    {
        if ($this->betterStandardPrinter->areNodesEqual(
            $notIdentical->left,
            $returnNode->expr
        ) && $this->constFetchManipulator->isNull($notIdentical->right)) {
            return $notIdentical->left;
        }

        if (! $this->betterStandardPrinter->areNodesEqual($notIdentical->right, $returnNode->expr)) {
            return null;
        }
        if ($this->constFetchManipulator->isNull($notIdentical->left)) {
            return $notIdentical->right;
        }

        return null;
    }

    private function isIfWithoutElseAndElseIfs(If_ $if): bool
    {
        if ($if->else !== null) {
            return false;
        }

        return ! (bool) $if->elseifs;
    }
}
