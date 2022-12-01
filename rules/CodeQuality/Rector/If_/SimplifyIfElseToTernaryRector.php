<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use RectorPrefix202212\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector\SimplifyIfElseToTernaryRectorTest
 */
final class SimplifyIfElseToTernaryRector extends AbstractRector
{
    /**
     * @var int
     */
    private const LINE_LENGTH_LIMIT = 120;
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(NodePrinterInterface $nodePrinter)
    {
        $this->nodePrinter = $nodePrinter;
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
        if ($node->else === null) {
            return null;
        }
        if ($node->elseifs !== []) {
            return null;
        }
        $ifAssignVar = $this->resolveOnlyStmtAssignVar($node->stmts);
        if (!$ifAssignVar instanceof Expr) {
            return null;
        }
        $elseAssignVar = $this->resolveOnlyStmtAssignVar($node->else->stmts);
        if (!$elseAssignVar instanceof Expr) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($ifAssignVar, $elseAssignVar)) {
            return null;
        }
        $ternaryIf = $this->resolveOnlyStmtAssignExpr($node->stmts);
        $ternaryElse = $this->resolveOnlyStmtAssignExpr($node->else->stmts);
        if (!$ternaryIf instanceof Expr) {
            return null;
        }
        if (!$ternaryElse instanceof Expr) {
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
            $betterNodeFinderFindInstanceOf = $this->betterNodeFinder->findInstanceOf($node, Ternary::class);
            if ($betterNodeFinderFindInstanceOf !== []) {
                return \true;
            }
        }
        return \false;
    }
    private function isNodeTooLong(Assign $assign) : bool
    {
        $assignContent = $this->nodePrinter->print($assign);
        return Strings::length($assignContent) > self::LINE_LENGTH_LIMIT;
    }
}
