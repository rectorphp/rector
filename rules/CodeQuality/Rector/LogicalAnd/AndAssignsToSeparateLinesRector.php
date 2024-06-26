<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\LogicalAnd;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector\AndAssignsToSeparateLinesRectorTest
 */
final class AndAssignsToSeparateLinesRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Split 2 assigns ands to separate line', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Expression[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if (!$node->expr instanceof LogicalAnd) {
            return null;
        }
        $logicalAnd = $node->expr;
        if (!$logicalAnd->left instanceof Assign) {
            return null;
        }
        if (!$logicalAnd->right instanceof Assign) {
            return null;
        }
        $leftAssignExpression = new Expression($logicalAnd->left);
        $rightAssignExpression = new Expression($logicalAnd->right);
        return [$leftAssignExpression, $rightAssignExpression];
    }
}
