<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\Rector\Assign;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\CallLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Assign\SplitDoubleAssignRector\SplitDoubleAssignRectorTest
 */
final class SplitDoubleAssignRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Split multiple inline assigns to each own lines default value, to prevent undefined array issues', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $one = $two = 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $one = 1;
        $two = 1;
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
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $firstAssign = $node->expr;
        if (!$firstAssign->expr instanceof Assign) {
            return null;
        }
        $nestedAssign = $firstAssign->expr;
        $newAssign = new Assign($firstAssign->var, $nestedAssign->expr);
        $newAssignExpression = new Expression($newAssign);
        // avoid calling the same method/funtion/new twice
        if (!$nestedAssign->expr instanceof CallLike) {
            $varAssign = new Assign($nestedAssign->var, $nestedAssign->expr);
            return [$newAssignExpression, new Expression($varAssign)];
        }
        $varAssign = new Assign($nestedAssign->var, $firstAssign->var);
        return [$newAssignExpression, new Expression($varAssign)];
    }
}
