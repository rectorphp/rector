<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector\RemoveAndTrueRectorTest
 */
final class RemoveAndTrueRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove `and true` that has no added value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return true && 5 === 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5 === 1;
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
        return [BooleanAnd::class];
    }
    /**
     * @param BooleanAnd $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->isTrueOrBooleanAndTrues($node->left)) {
            return $node->right;
        }
        if ($this->isTrueOrBooleanAndTrues($node->right)) {
            return $node->left;
        }
        return null;
    }
    private function isTrueOrBooleanAndTrues(Expr $expr) : bool
    {
        if ($this->valueResolver->isTrue($expr)) {
            return \true;
        }
        if (!$expr instanceof BooleanAnd) {
            return \false;
        }
        if (!$this->isTrueOrBooleanAndTrues($expr->left)) {
            return \false;
        }
        return $this->isTrueOrBooleanAndTrues($expr->right);
    }
}
