<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector\PreparedValueToEarlyReturnRectorTest
 */
final class PreparedValueToEarlyReturnRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Return early prepared value in ifs', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $var = null;

        if (rand(0,1)) {
            $var = 1;
        }

        if (rand(0,1)) {
            $var = 2;
        }

        return $var;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (rand(0,1)) {
            return 1;
        }

        if (rand(0,1)) {
            return 2;
        }

        return null;
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
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $ifsBefore = $this->getIfsBefore($node);
        if ($this->shouldSkip($ifsBefore, $node->expr)) {
            return null;
        }
        if ($this->isAssignVarUsedInIfCond($ifsBefore, $node->expr)) {
            return null;
        }
        /** @var Expr $returnExpr */
        $returnExpr = $node->expr;
        /** @var Expression $previousFirstExpression */
        $previousFirstExpression = $this->getPreviousIfLinearEquals($ifsBefore[0], $returnExpr);
        /** @var Assign $previousAssign */
        $previousAssign = $previousFirstExpression->expr;
        if ($this->isPreviousVarUsedInAssignExpr($ifsBefore, $previousAssign->var)) {
            return null;
        }
        foreach ($ifsBefore as $ifBefore) {
            /** @var Expression $expressionIf */
            $expressionIf = $ifBefore->stmts[0];
            /** @var Assign $assignIf */
            $assignIf = $expressionIf->expr;
            $ifBefore->stmts[0] = new \PhpParser\Node\Stmt\Return_($assignIf->expr);
        }
        /** @var Assign $assignPrevious */
        $assignPrevious = $previousFirstExpression->expr;
        $node->expr = $assignPrevious->expr;
        $this->removeNode($previousFirstExpression);
        return $node;
    }
    /**
     * @param If_[] $ifsBefore
     */
    private function isAssignVarUsedInIfCond(array $ifsBefore, ?\PhpParser\Node\Expr $expr) : bool
    {
        foreach ($ifsBefore as $ifBefore) {
            $isUsedInIfCond = (bool) $this->betterNodeFinder->findFirst($ifBefore->cond, function (\PhpParser\Node $node) use($expr) : bool {
                return $this->nodeComparator->areNodesEqual($node, $expr);
            });
            if ($isUsedInIfCond) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param If_[] $ifsBefore
     */
    private function isPreviousVarUsedInAssignExpr(array $ifsBefore, \PhpParser\Node\Expr $expr) : bool
    {
        foreach ($ifsBefore as $ifBefore) {
            /** @var Expression $expression */
            $expression = $ifBefore->stmts[0];
            /** @var Assign $assign */
            $assign = $expression->expr;
            $isUsedInAssignExpr = (bool) $this->betterNodeFinder->findFirst($assign->expr, function (\PhpParser\Node $node) use($expr) : bool {
                return $this->nodeComparator->areNodesEqual($node, $expr);
            });
            if ($isUsedInAssignExpr) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param If_[] $ifsBefore
     */
    private function shouldSkip(array $ifsBefore, ?\PhpParser\Node\Expr $returnExpr) : bool
    {
        if ($ifsBefore === []) {
            return \true;
        }
        return !(bool) $this->getPreviousIfLinearEquals($ifsBefore[0], $returnExpr);
    }
    private function getPreviousIfLinearEquals(?\PhpParser\Node $node, ?\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Stmt\Expression
    {
        if (!$node instanceof \PhpParser\Node) {
            return null;
        }
        if (!$expr instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $previous = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if (!$previous instanceof \PhpParser\Node\Stmt\Expression) {
            return $this->getPreviousIfLinearEquals($previous, $expr);
        }
        if (!$previous->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        if ($this->nodeComparator->areNodesEqual($previous->expr->var, $expr)) {
            return $previous;
        }
        return null;
    }
    /**
     * @return If_[]
     */
    private function getIfsBefore(\PhpParser\Node\Stmt\Return_ $return) : array
    {
        $parent = $return->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\FunctionLike && !$parent instanceof \PhpParser\Node\Stmt\If_) {
            return [];
        }
        if ($parent->stmts === []) {
            return [];
        }
        \end($parent->stmts);
        $firstItemPosition = \key($parent->stmts);
        if ($parent->stmts[$firstItemPosition] !== $return) {
            return [];
        }
        return $this->collectIfs($parent->stmts, $return);
    }
    /**
     * @param If_[] $stmts
     * @return If_[]
     */
    private function collectIfs(array $stmts, \PhpParser\Node\Stmt\Return_ $return) : array
    {
        /** @va If_[] $ifs */
        $ifs = $this->betterNodeFinder->findInstanceOf($stmts, \PhpParser\Node\Stmt\If_::class);
        /** Skip entirely if found skipped ifs */
        foreach ($ifs as $if) {
            /** @var If_ $if */
            if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($if)) {
                return [];
            }
            $stmts = $if->stmts;
            if (\count($stmts) !== 1) {
                return [];
            }
            $expression = $stmts[0];
            if (!$expression instanceof \PhpParser\Node\Stmt\Expression) {
                return [];
            }
            if (!$expression->expr instanceof \PhpParser\Node\Expr\Assign) {
                return [];
            }
            $assign = $expression->expr;
            if (!$this->nodeComparator->areNodesEqual($assign->var, $return->expr)) {
                return [];
            }
        }
        return $ifs;
    }
}
