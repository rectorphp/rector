<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use RectorPrefix202308\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector\SimplifyIfElseToTernaryRectorTest
 */
final class SimplifyIfElseToTernaryRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @var int
     */
    private const LINE_LENGTH_LIMIT = 120;
    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes if/else for same value as assign to ternary', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->else instanceof Else_) {
            return null;
        }
        if ($node->elseifs !== []) {
            return null;
        }
        $ifAssignVarExpr = $this->resolveOnlyStmtAssignVar($node->stmts);
        if (!$ifAssignVarExpr instanceof Expr) {
            return null;
        }
        $elseAssignExpr = $this->resolveOnlyStmtAssignVar($node->else->stmts);
        if (!$elseAssignExpr instanceof Expr) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($ifAssignVarExpr, $elseAssignExpr)) {
            return null;
        }
        $ternaryIfExpr = $this->resolveOnlyStmtAssignExpr($node->stmts);
        $expr = $this->resolveOnlyStmtAssignExpr($node->else->stmts);
        if (!$ternaryIfExpr instanceof Expr) {
            return null;
        }
        if (!$expr instanceof Expr) {
            return null;
        }
        // has nested ternary → skip, it's super hard to read
        if ($this->haveNestedTernary([$node->cond, $ternaryIfExpr, $expr])) {
            return null;
        }
        $ternary = new Ternary($node->cond, $ternaryIfExpr, $expr);
        $assign = new Assign($ifAssignVarExpr, $ternary);
        // do not create super long lines
        if ($this->isNodeTooLong($assign)) {
            return null;
        }
        $expression = new Expression($assign);
        $this->mirrorComments($expression, $node);
        return $expression;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignVar(array $stmts) : ?Expr
    {
        if (\count($stmts) !== 1) {
            return null;
        }
        $stmt = $stmts[0];
        if (!$stmt instanceof Expression) {
            return null;
        }
        $stmtExpr = $stmt->expr;
        if (!$stmtExpr instanceof Assign) {
            return null;
        }
        return $stmtExpr->var;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignExpr(array $stmts) : ?Expr
    {
        if (\count($stmts) !== 1) {
            return null;
        }
        $stmt = $stmts[0];
        if (!$stmt instanceof Expression) {
            return null;
        }
        $stmtExpr = $stmt->expr;
        if (!$stmtExpr instanceof Assign) {
            return null;
        }
        return $stmtExpr->expr;
    }
    /**
     * @param Node[] $nodes
     */
    private function haveNestedTernary(array $nodes) : bool
    {
        foreach ($nodes as $node) {
            $ternary = $this->betterNodeFinder->findFirstInstanceOf($node, Ternary::class);
            if ($ternary instanceof Ternary) {
                return \true;
            }
        }
        return \false;
    }
    private function isNodeTooLong(Assign $assign) : bool
    {
        $assignContent = $this->betterStandardPrinter->print($assign);
        return Strings::length($assignContent) > self::LINE_LENGTH_LIMIT;
    }
}
