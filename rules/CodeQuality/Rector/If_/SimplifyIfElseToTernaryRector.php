<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use RectorPrefix20220501\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(\Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter)
    {
        $this->nodePrinter = $nodePrinter;
    }
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
        if (!$ifAssignVar instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $elseAssignVar = $this->resolveOnlyStmtAssignVar($node->else->stmts);
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
        if ($this->isNextReturnRemoved($node, $ifAssignVar)) {
            return null;
        }
        $expression = new \PhpParser\Node\Stmt\Expression($assign);
        $this->mirrorComments($expression, $node);
        return $expression;
    }
    private function isNextReturnRemoved(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr $expr) : bool
    {
        if (!$this->nodesToRemoveCollector->isActive()) {
            return \false;
        }
        $next = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if ($next instanceof \PhpParser\Node\Stmt\Return_ && $next->expr instanceof \PhpParser\Node\Expr && $this->nodeComparator->areNodesEqual($next->expr, $expr)) {
            $nodesToRemove = $this->nodesToRemoveCollector->getNodesToRemove();
            foreach ($nodesToRemove as $nodeToRemove) {
                if ($this->nodeComparator->areNodesEqual($next, $nodeToRemove)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignVar(array $stmts) : ?\PhpParser\Node\Expr
    {
        if (\count($stmts) !== 1) {
            return null;
        }
        $stmt = $stmts[0];
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $stmtExpr = $stmt->expr;
        if (!$stmtExpr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        return $stmtExpr->var;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignExpr(array $stmts) : ?\PhpParser\Node\Expr
    {
        if (\count($stmts) !== 1) {
            return null;
        }
        $stmt = $stmts[0];
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $stmtExpr = $stmt->expr;
        if (!$stmtExpr instanceof \PhpParser\Node\Expr\Assign) {
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
            $betterNodeFinderFindInstanceOf = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Expr\Ternary::class);
            if ($betterNodeFinderFindInstanceOf !== []) {
                return \true;
            }
        }
        return \false;
    }
    private function isNodeTooLong(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        $assignContent = $this->nodePrinter->print($assign);
        return \RectorPrefix20220501\Nette\Utils\Strings::length($assignContent) > self::LINE_LENGTH_LIMIT;
    }
}
