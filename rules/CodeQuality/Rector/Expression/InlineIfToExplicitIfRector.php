<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\BooleanType;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/dmHCC
 *
 * @see \Rector\Tests\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector\InlineIfToExplicitIfRectorTest
 */
final class InlineIfToExplicitIfRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(\Rector\Core\NodeManipulator\BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change inline if to explicit if', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $userId = null;

        is_null($userId) && $userId = 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $userId = null;

        if (is_null($userId)) {
            $userId = 5;
        }
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
        if ($node->expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
            return $this->processExplicitIf($node);
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return $this->processExplicitIf($node);
        }
        return null;
    }
    private function processExplicitIf(\PhpParser\Node\Stmt\Expression $expression) : ?\PhpParser\Node
    {
        /** @var BooleanAnd|BooleanOr $booleanExpr */
        $booleanExpr = $expression->expr;
        $leftStaticType = $this->getType($booleanExpr->left);
        if (!$leftStaticType instanceof \PHPStan\Type\BooleanType) {
            return null;
        }
        $exprLeft = $booleanExpr->left instanceof \PhpParser\Node\Expr\BooleanNot ? $booleanExpr->left->expr : $booleanExpr->left;
        if ($exprLeft instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($exprLeft, 'defined')) {
            return null;
        }
        /** @var Expr $expr */
        $expr = $booleanExpr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd ? $booleanExpr->left : $this->binaryOpManipulator->inverseNode($booleanExpr->left);
        $if = new \PhpParser\Node\Stmt\If_($expr);
        $if->stmts[] = new \PhpParser\Node\Stmt\Expression($booleanExpr->right);
        $this->mirrorComments($if, $expression);
        return $if;
    }
}
