<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use RectorPrefix20211231\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector\SimplifyIfElseToTernaryRectorTest
 */
final class SimplifyIfElseToTernaryRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var int
     */
    private const LINE_LENGTH_LIMIT = 120;
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes if/else for same value as assign to ternary', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->arrayBuilt[][$key] = empty($value) ? true : $value;
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->else === null) {
            return null;
        }
        if ($node->elseifs !== []) {
            return null;
        }
        $ifAssignVar = $this->resolveOnlyStmtAssignVar($node->stmts);
        $elseAssignVar = $this->resolveOnlyStmtAssignVar($node->else->stmts);
        if (!$ifAssignVar instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if (!$elseAssignVar instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($ifAssignVar, $elseAssignVar)) {
            return null;
        }
        $ternaryIf = $this->resolveOnlyStmtAssignExpr($node->stmts);
        $ternaryElse = $this->resolveOnlyStmtAssignExpr($node->else->stmts);
        if (!$ternaryIf instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if (!$ternaryElse instanceof \PhpParser\Node\Expr) {
            return null;
        }
        // has nested ternary → skip, it's super hard to read
        if ($this->haveNestedTernary([$node->cond, $ternaryIf, $ternaryElse])) {
            return null;
        }
        $ternary = new \PhpParser\Node\Expr\Ternary($node->cond, $ternaryIf, $ternaryElse);
        $assign = new \PhpParser\Node\Expr\Assign($ifAssignVar, $ternary);
        // do not create super long lines
        if ($this->isNodeTooLong($assign)) {
            return null;
        }
        $expression = new \PhpParser\Node\Stmt\Expression($assign);
        $this->mirrorComments($expression, $node);
        return $expression;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignVar(array $stmts) : ?\PhpParser\Node\Expr
    {
        if (\count($stmts) !== 1) {
            return null;
        }
        $onlyStmt = $this->unwrapExpression($stmts[0]);
        if (!$onlyStmt instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        return $onlyStmt->var;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignExpr(array $stmts) : ?\PhpParser\Node\Expr
    {
        if (\count($stmts) !== 1) {
            return null;
        }
        $onlyStmt = $this->unwrapExpression($stmts[0]);
        if (!$onlyStmt instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        return $onlyStmt->expr;
    }
    /**
     * @param Node[] $nodes
     */
    private function haveNestedTernary(array $nodes) : bool
    {
        foreach ($nodes as $node) {
            $betterNodeFinderFindInstanceOf = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Expr\Ternary::class);
            if ($betterNodeFinderFindInstanceOf !== []) {
                return \true;
            }
        }
        return \false;
    }
    private function isNodeTooLong(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        return \RectorPrefix20211231\Nette\Utils\Strings::length($this->print($assign)) > self::LINE_LENGTH_LIMIT;
    }
}
