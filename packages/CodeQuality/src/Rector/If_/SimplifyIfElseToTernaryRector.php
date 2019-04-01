<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyIfElseToTernaryRector extends AbstractRector
{
    /**
     * @var int
     */
    private const LINE_LENGHT_LIMIT = 120;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes if/else for same value as assign to ternary', [
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

        if (count($node->elseifs) > 0) {
            return null;
        }

        $ifAssignVar = $this->resolveOnlyStmtAssignVar($node->stmts);
        $elseAssignVar = $this->resolveOnlyStmtAssignVar($node->else->stmts);

        if ($ifAssignVar === null || $elseAssignVar === null) {
            return null;
        }

        if (! $this->areNodesEqual($ifAssignVar, $elseAssignVar)) {
            return null;
        }

        $ternaryIf = $this->resolveOnlyStmtAssignExpr($node->stmts);
        $ternaryElse = $this->resolveOnlyStmtAssignExpr($node->else->stmts);
        if ($ternaryIf === null || $ternaryElse === null) {
            return null;
        }

        // has nested ternary → skip, it's super hard to read
        if ($this->hasNestedTernary($ternaryIf, $ternaryElse)) {
            return null;
        }

        $ternary = new Ternary($node->cond, $ternaryIf, $ternaryElse);
        $assign = new Assign($ifAssignVar, $ternary);

        // do not create super long lines
        if (Strings::length($this->print($assign)) > self::LINE_LENGHT_LIMIT) {
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

    private function unwrapExpression(Node $node): Node
    {
        return $node instanceof Expression ? $node->expr : $node;
    }

    private function hasNestedTernary(Expr $ternaryIf, Expr $ternaryElse): bool
    {
        if ($this->betterNodeFinder->findInstanceOf($ternaryIf, Ternary::class)) {
            return true;
        }

        return (bool) $this->betterNodeFinder->findInstanceOf($ternaryElse, Ternary::class);
    }
}
