<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
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
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\CoalesceAnalyzer
     */
    private $coalesceAnalyzer;
    /**
     * @readonly
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade throw expression', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
echo $variable ?? throw new RuntimeException();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if (! isset($variable)) {
    throw new RuntimeException();
}

echo $variable;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Expression::class, \PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Expression|Return_ $node
     * @return Node|Node[]|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Stmt\Return_) {
            return $this->refactorReturn($node);
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\Throw_) {
            return null;
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\Assign) {
            return $this->refactorAssign($node, $node->expr);
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
            return $this->refactorCoalesce($node->expr, null);
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\Ternary) {
            return $this->refactorTernary($node->expr, null);
        }
        return null;
    }
    /**
     * @return mixed[]|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\If_|null
     */
    private function refactorAssign(\PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr\Assign $assign)
    {
        if (!$this->hasThrowInAssignExpr($assign)) {
            return null;
        }
        if ($assign->expr instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
            return $this->refactorCoalesce($assign->expr, $assign);
        }
        if ($assign->expr instanceof \PhpParser\Node\Expr\Throw_) {
            return new \PhpParser\Node\Stmt\Expression($assign->expr);
        }
        if ($assign->expr instanceof \PhpParser\Node\Expr\Ternary) {
            return $this->refactorTernary($assign->expr, $assign);
        }
        return $expression;
    }
    /**
     * @return mixed[]|\PhpParser\Node\Stmt\If_|null
     */
    private function refactorTernary(\PhpParser\Node\Expr\Ternary $ternary, ?\PhpParser\Node\Expr\Assign $assign)
    {
        if (!$ternary->else instanceof \PhpParser\Node\Expr\Throw_) {
            return null;
        }
        $inversedTernaryCond = $this->binaryOpManipulator->inverseNode($ternary->cond);
        $if = $this->ifManipulator->createIfExpr($inversedTernaryCond, new \PhpParser\Node\Stmt\Expression($ternary->else));
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            return $if;
        }
        $assign->expr = $ternary->if ?? $ternary->cond;
        return [$if, new \PhpParser\Node\Stmt\Expression($assign)];
    }
    /**
     * @return mixed[]|\PhpParser\Node\Stmt\If_|null
     */
    private function refactorCoalesce(\PhpParser\Node\Expr\BinaryOp\Coalesce $coalesce, ?\PhpParser\Node\Expr\Assign $assign)
    {
        if (!$coalesce->right instanceof \PhpParser\Node\Expr\Throw_) {
            return null;
        }
        if (!$this->coalesceAnalyzer->hasIssetableLeft($coalesce)) {
            return null;
        }
        $condExpr = $this->createCondExpr($coalesce);
        $if = $this->ifManipulator->createIfExpr($condExpr, new \PhpParser\Node\Stmt\Expression($coalesce->right));
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            return $if;
        }
        $assign->expr = $coalesce->left;
        return [$if, new \PhpParser\Node\Stmt\Expression($assign)];
    }
    private function hasThrowInAssignExpr(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($assign->expr, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Expr\Throw_;
        });
    }
    /**
     * @return Node[]|null
     */
    private function refactorReturn(\PhpParser\Node\Stmt\Return_ $return) : ?array
    {
        $throwExpr = $this->betterNodeFinder->findFirstInstanceOf($return, \PhpParser\Node\Expr\Throw_::class);
        if (!$throwExpr instanceof \PhpParser\Node\Expr\Throw_) {
            return null;
        }
        if ($return->expr instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
            $coalesce = $return->expr;
            if (!$coalesce->right instanceof \PhpParser\Node\Expr\Throw_) {
                return null;
            }
            $if = $this->createIf($coalesce, $coalesce->right);
            return [$if, new \PhpParser\Node\Stmt\Return_($coalesce->left)];
        }
        return null;
    }
    private function createIf(\PhpParser\Node\Expr\BinaryOp\Coalesce $coalesce, \PhpParser\Node\Expr\Throw_ $throw) : \PhpParser\Node\Stmt\If_
    {
        $booleanNot = new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\Isset_([$coalesce->left]));
        return new \PhpParser\Node\Stmt\If_($booleanNot, ['stmts' => [new \PhpParser\Node\Stmt\Expression($throw)]]);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BooleanNot
     */
    private function createCondExpr(\PhpParser\Node\Expr\BinaryOp\Coalesce $coalesce)
    {
        if ($coalesce->left instanceof \PhpParser\Node\Expr\Variable || $coalesce->left instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\Isset_([$coalesce->left]));
        }
        return new \PhpParser\Node\Expr\BinaryOp\Identical($coalesce->left, $this->nodeFactory->createNull());
    }
}
