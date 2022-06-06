<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\Rector\LogicalAnd;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\LogicalOr;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/5998330/1348344
 *
 * @see \Rector\Tests\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector\LogicalToBooleanRectorTest
 */
final class LogicalToBooleanRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change OR, AND to ||, && with more common understanding', [new CodeSample(<<<'CODE_SAMPLE'
if ($f = false or true) {
    return $f;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if (($f = false) || true) {
    return $f;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [LogicalOr::class, LogicalAnd::class];
    }
    /**
     * @param LogicalOr|LogicalAnd $node
     */
    public function refactor(Node $node) : ?Node
    {
        return $this->refactorLogicalToBoolean($node);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\LogicalOr|\PhpParser\Node\Expr\BinaryOp\LogicalAnd $node
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr
     */
    private function refactorLogicalToBoolean($node)
    {
        if ($node->left instanceof LogicalOr || $node->left instanceof LogicalAnd) {
            $node->left = $this->refactorLogicalToBoolean($node->left);
        }
        if ($node->right instanceof LogicalOr || $node->right instanceof LogicalAnd) {
            $node->right = $this->refactorLogicalToBoolean($node->right);
        }
        if ($node instanceof LogicalOr) {
            return new BooleanOr($node->left, $node->right);
        }
        return new BooleanAnd($node->left, $node->right);
    }
}
