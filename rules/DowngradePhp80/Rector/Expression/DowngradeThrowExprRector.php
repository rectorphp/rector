<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\NodeAnalyzer\CoalesceAnalyzer;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/throw_expression
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\DowngradeThrowExprRectorTest
 */
final class DowngradeThrowExprRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @var \Rector\Core\NodeAnalyzer\CoalesceAnalyzer
     */
    private $coalesceAnalyzer;
    /**
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\Core\NodeAnalyzer\CoalesceAnalyzer $coalesceAnalyzer, \Rector\Core\NodeManipulator\BinaryOpManipulator $binaryOpManipulator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->coalesceAnalyzer = $coalesceAnalyzer;
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade throw as expr', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $id = $somethingNonexistent ?? throw new RuntimeException();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (!isset($somethingNonexistent)) {
            throw new RuntimeException();
        }
        $id = $somethingNonexistent;
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
        return [\PhpParser\Node\Stmt\Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->expr instanceof \PhpParser\Node\Expr\Throw_) {
            return null;
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\Assign) {
            return $this->processAssign($node, $node->expr);
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
            return $this->processCoalesce($node->expr, null);
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\Ternary) {
            return $this->processTernary($node->expr, null);
        }
        return $node;
    }
    /**
     * @return \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\Expression|null
     */
    private function processAssign(\PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr\Assign $assign)
    {
        if (!$this->hasThrowInAssignExpr($assign)) {
            return null;
        }
        if ($assign->expr instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
            return $this->processCoalesce($assign->expr, $assign);
        }
        if ($assign->expr instanceof \PhpParser\Node\Expr\Throw_) {
            return new \PhpParser\Node\Stmt\Expression($assign->expr);
        }
        if ($assign->expr instanceof \PhpParser\Node\Expr\Ternary) {
            return $this->processTernary($assign->expr, $assign);
        }
        return $expression;
    }
    private function processTernary(\PhpParser\Node\Expr\Ternary $ternary, ?\PhpParser\Node\Expr\Assign $assign) : ?\PhpParser\Node\Stmt\If_
    {
        if (!$ternary->else instanceof \PhpParser\Node\Expr\Throw_) {
            return null;
        }
        $inversedTernaryCond = $this->binaryOpManipulator->inverseNode($ternary->cond);
        if (!$inversedTernaryCond instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $if = $this->ifManipulator->createIfExpr($inversedTernaryCond, new \PhpParser\Node\Stmt\Expression($ternary->else));
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            return $if;
        }
        $assign->expr = $ternary->if ?? $ternary->cond;
        $this->nodesToAddCollector->addNodeAfterNode(new \PhpParser\Node\Stmt\Expression($assign), $if);
        return $if;
    }
    private function processCoalesce(\PhpParser\Node\Expr\BinaryOp\Coalesce $coalesce, ?\PhpParser\Node\Expr\Assign $assign) : ?\PhpParser\Node\Stmt\If_
    {
        if (!$coalesce->right instanceof \PhpParser\Node\Expr\Throw_) {
            return null;
        }
        if (!$this->coalesceAnalyzer->hasIssetableLeft($coalesce)) {
            return null;
        }
        $booleanNot = new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\Isset_([$coalesce->left]));
        $if = $this->ifManipulator->createIfExpr($booleanNot, new \PhpParser\Node\Stmt\Expression($coalesce->right));
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            return $if;
        }
        $assign->expr = $coalesce->left;
        $this->nodesToAddCollector->addNodeAfterNode(new \PhpParser\Node\Stmt\Expression($assign), $if);
        return $if;
    }
    private function hasThrowInAssignExpr(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($assign->expr, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Expr\Throw_;
        });
    }
}
