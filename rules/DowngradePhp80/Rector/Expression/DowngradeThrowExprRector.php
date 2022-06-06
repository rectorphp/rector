<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\Rector\Expression;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Coalesce;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Identical;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\Isset_;
use RectorPrefix20220606\PhpParser\Node\Expr\Ternary;
use RectorPrefix20220606\PhpParser\Node\Expr\Throw_;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\CoalesceAnalyzer;
use RectorPrefix20220606\Rector\Core\NodeManipulator\BinaryOpManipulator;
use RectorPrefix20220606\Rector\Core\NodeManipulator\IfManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/throw_expression
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\DowngradeThrowExprRectorTest
 */
final class DowngradeThrowExprRector extends AbstractRector
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
    public function __construct(IfManipulator $ifManipulator, CoalesceAnalyzer $coalesceAnalyzer, BinaryOpManipulator $binaryOpManipulator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->coalesceAnalyzer = $coalesceAnalyzer;
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade throw expression', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Expression::class, Return_::class];
    }
    /**
     * @param Expression|Return_ $node
     * @return Node|Node[]|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Return_) {
            return $this->refactorReturn($node);
        }
        if ($node->expr instanceof Throw_) {
            return null;
        }
        if ($node->expr instanceof Assign) {
            return $this->refactorAssign($node, $node->expr);
        }
        if ($node->expr instanceof Coalesce) {
            return $this->refactorCoalesce($node->expr, null);
        }
        if ($node->expr instanceof Ternary) {
            return $this->refactorTernary($node->expr, null);
        }
        return null;
    }
    /**
     * @return \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\Expression|null|mixed[]
     */
    private function refactorAssign(Expression $expression, Assign $assign)
    {
        if (!$this->hasThrowInAssignExpr($assign)) {
            return null;
        }
        if ($assign->expr instanceof Coalesce) {
            return $this->refactorCoalesce($assign->expr, $assign);
        }
        if ($assign->expr instanceof Throw_) {
            return new Expression($assign->expr);
        }
        if ($assign->expr instanceof Ternary) {
            return $this->refactorTernary($assign->expr, $assign);
        }
        return $expression;
    }
    /**
     * @return \PhpParser\Node\Stmt\If_|null|mixed[]
     */
    private function refactorTernary(Ternary $ternary, ?Assign $assign)
    {
        if (!$ternary->else instanceof Throw_) {
            return null;
        }
        $inversedTernaryCond = $this->binaryOpManipulator->inverseNode($ternary->cond);
        $if = $this->ifManipulator->createIfStmt($inversedTernaryCond, new Expression($ternary->else));
        if (!$assign instanceof Assign) {
            return $if;
        }
        $assign->expr = $ternary->if ?? $ternary->cond;
        return [$if, new Expression($assign)];
    }
    /**
     * @return \PhpParser\Node\Stmt\If_|null|mixed[]
     */
    private function refactorCoalesce(Coalesce $coalesce, ?Assign $assign)
    {
        if (!$coalesce->right instanceof Throw_) {
            return null;
        }
        if (!$this->coalesceAnalyzer->hasIssetableLeft($coalesce)) {
            return null;
        }
        $condExpr = $this->createCondExpr($coalesce);
        $if = $this->ifManipulator->createIfStmt($condExpr, new Expression($coalesce->right));
        if (!$assign instanceof Assign) {
            return $if;
        }
        $assign->expr = $coalesce->left;
        return [$if, new Expression($assign)];
    }
    private function hasThrowInAssignExpr(Assign $assign) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($assign->expr, function (Node $node) : bool {
            return $node instanceof Throw_;
        });
    }
    /**
     * @return Node[]|null
     */
    private function refactorReturn(Return_ $return) : ?array
    {
        $throwExpr = $this->betterNodeFinder->findFirstInstanceOf($return, Throw_::class);
        if (!$throwExpr instanceof Throw_) {
            return null;
        }
        if ($return->expr instanceof Coalesce) {
            $coalesce = $return->expr;
            if (!$coalesce->right instanceof Throw_) {
                return null;
            }
            $if = $this->createIf($coalesce, $coalesce->right);
            return [$if, new Return_($coalesce->left)];
        }
        return null;
    }
    private function createIf(Coalesce $coalesce, Throw_ $throw) : If_
    {
        $booleanNot = new BooleanNot(new Isset_([$coalesce->left]));
        return new If_($booleanNot, ['stmts' => [new Expression($throw)]]);
    }
    /**
     * @return \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\BinaryOp\Identical
     */
    private function createCondExpr(Coalesce $coalesce)
    {
        if ($coalesce->left instanceof Variable || $coalesce->left instanceof ArrayDimFetch) {
            return new BooleanNot(new Isset_([$coalesce->left]));
        }
        return new Identical($coalesce->left, $this->nodeFactory->createNull());
    }
}
