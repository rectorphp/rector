<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\LogicalAnd;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/ji8bX
 * @see \Rector\Tests\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector\AndAssignsToSeparateLinesRectorTest
 */
final class AndAssignsToSeparateLinesRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Split 2 assigns ands to separate line', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $tokens = [];
        $token = 4 and $tokens[] = $token;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $tokens = [];
        $token = 4;
        $tokens[] = $token;
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
     * @return Expression[]|null
     */
    public function refactor(\PhpParser\Node $node) : ?array
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\BinaryOp\LogicalAnd) {
            return null;
        }
        $logicalAnd = $node->expr;
        if (!$logicalAnd->left instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        if (!$logicalAnd->right instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $leftAssignExpression = new \PhpParser\Node\Stmt\Expression($logicalAnd->left);
        $rightAssignExpression = new \PhpParser\Node\Stmt\Expression($logicalAnd->right);
        return [$leftAssignExpression, $rightAssignExpression];
    }
}
