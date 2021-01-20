<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\SimplifyIfElseToTernaryRector\SimplifyIfElseToTernaryRectorTest
 */
final class SimplifyIfElseToTernaryRector extends AbstractRector
{
    /**
     * @var int
     */
    private const LINE_LENGTH_LIMIT = 120;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes if/else for same value as assign to ternary',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (empty($value)) {
            $this->arrayBuilt[][$key] = true;
        } else {
            $this->arrayBuilt[][$key] = $value;
        }
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->arrayBuilt[][$key] = empty($value) ? true : $value;
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->else === null) {
            return null;
        }

        if ($node->elseifs !== []) {
            return null;
        }

        $ifAssignVar = $this->resolveOnlyStmtAssignVar($node->stmts);
        $elseAssignVar = $this->resolveOnlyStmtAssignVar($node->else->stmts);
        if (! $ifAssignVar instanceof Expr) {
            return null;
        }
        if (! $elseAssignVar instanceof Expr) {
            return null;
        }

        if (! $this->areNodesEqual($ifAssignVar, $elseAssignVar)) {
            return null;
        }

        $ternaryIf = $this->resolveOnlyStmtAssignExpr($node->stmts);
        $ternaryElse = $this->resolveOnlyStmtAssignExpr($node->else->stmts);
        if (! $ternaryIf instanceof Expr) {
            return null;
        }
        if (! $ternaryElse instanceof Expr) {
            return null;
        }

        // has nested ternary → skip, it's super hard to read
        if ($this->haveNestedTernary([$node->cond, $ternaryIf, $ternaryElse])) {
            return null;
        }

        $ternary = new Ternary($node->cond, $ternaryIf, $ternaryElse);
        $assign = new Assign($ifAssignVar, $ternary);

        // do not create super long lines
        if ($this->isNodeTooLong($assign)) {
            return null;
        }

        return $assign;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignVar(array $stmts): ?Expr
    {
        if (count($stmts) !== 1) {
            return null;
        }

        $onlyStmt = $this->unwrapExpression($stmts[0]);
        if (! $onlyStmt instanceof Assign) {
            return null;
        }

        return $onlyStmt->var;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignExpr(array $stmts): ?Expr
    {
        if (count($stmts) !== 1) {
            return null;
        }

        $onlyStmt = $this->unwrapExpression($stmts[0]);
        if (! $onlyStmt instanceof Assign) {
            return null;
        }

        return $onlyStmt->expr;
    }

    /**
     * @param Node[] $nodes
     */
    private function haveNestedTernary(array $nodes): bool
    {
        foreach ($nodes as $node) {
            $betterNodeFinderFindInstanceOf = $this->betterNodeFinder->findInstanceOf($node, Ternary::class);
            if ($betterNodeFinderFindInstanceOf !== []) {
                return true;
            }
        }

        return false;
    }

    private function isNodeTooLong(Assign $assign): bool
    {
        return Strings::length($this->print($assign)) > self::LINE_LENGTH_LIMIT;
    }
}
