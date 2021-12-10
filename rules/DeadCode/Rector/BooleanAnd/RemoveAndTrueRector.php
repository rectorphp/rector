<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector\RemoveAndTrueRectorTest
 */
final class RemoveAndTrueRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove and true that has no added value', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\BinaryOp\BooleanAnd::class];
    }
    /**
     * @param BooleanAnd $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->isTrueOrBooleanAndTrues($node->left)) {
            return $node->right;
        }
        if ($this->isTrueOrBooleanAndTrues($node->right)) {
            return $node->left;
        }
        return null;
    }
    private function isTrueOrBooleanAndTrues(\PhpParser\Node\Expr $expr) : bool
    {
        if ($this->valueResolver->isTrue($expr)) {
            return \true;
        }
        if (!$expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
            return \false;
        }
        if (!$this->isTrueOrBooleanAndTrues($expr->left)) {
            return \false;
        }
        return $this->isTrueOrBooleanAndTrues($expr->right);
    }
}
