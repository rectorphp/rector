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
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/dmHCC
 *
 * @see \Rector\Tests\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector\InlineIfToExplicitIfRectorTest
 */
final class InlineIfToExplicitIfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change inline if to explicit if', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->expr instanceof BooleanAnd) {
            return $this->processExplicitIf($node);
        }
        if ($node->expr instanceof BooleanOr) {
            return $this->processExplicitIf($node);
        }
        return null;
    }
    private function processExplicitIf(Expression $expression) : ?Node
    {
        /** @var BooleanAnd|BooleanOr $booleanExpr */
        $booleanExpr = $expression->expr;
        $leftStaticType = $this->getType($booleanExpr->left);
        if (!$leftStaticType->isBoolean()->yes()) {
            return null;
        }
        $exprLeft = $booleanExpr->left instanceof BooleanNot ? $booleanExpr->left->expr : $booleanExpr->left;
        if ($exprLeft instanceof FuncCall && $this->isName($exprLeft, 'defined')) {
            return null;
        }
        /** @var Expr $expr */
        $expr = $booleanExpr instanceof BooleanAnd ? $booleanExpr->left : $this->binaryOpManipulator->inverseNode($booleanExpr->left);
        $if = new If_($expr);
        $if->stmts[] = new Expression($booleanExpr->right);
        $this->mirrorComments($if, $expression);
        return $if;
    }
}
