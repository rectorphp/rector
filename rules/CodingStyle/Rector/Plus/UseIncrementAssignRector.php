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
final class UseIncrementAssignRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use ++ increment instead of `$var += 1`', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Plus::class, Minus::class];
    }
    /**
     * @param Plus|Minus $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->expr instanceof LNumber) {
            return null;
        }
        if ($node->expr->value !== 1) {
            return null;
        }
        if ($node instanceof Plus) {
            return new PreInc($node->var);
        }
        return new PreDec($node->var);
    }
}
