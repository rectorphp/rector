<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Plus;

use PhpParser\Node;
use PhpParser\Node\Expr\AssignOp\Minus;
use PhpParser\Node\Expr\AssignOp\Plus;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Plus\UseIncrementAssignRector\UseIncrementAssignRectorTest
 */
final class UseIncrementAssignRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use ++ increment instead of `$var += 1`', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $style += 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        ++$style;
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
        return [\PhpParser\Node\Expr\AssignOp\Plus::class, \PhpParser\Node\Expr\AssignOp\Minus::class];
    }
    /**
     * @param Plus|Minus $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->expr instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($node->expr->value !== 1) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\AssignOp\Plus) {
            return new \PhpParser\Node\Expr\PreInc($node->var);
        }
        return new \PhpParser\Node\Expr\PreDec($node->var);
    }
}
