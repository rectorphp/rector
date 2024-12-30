<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\LogicalAnd;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
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
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr|null
     */
    public function refactor(Node $node)
    {
        $type = $this->nodeTypeResolver->getNativeType($node->left);
        if ($node->left instanceof Assign && !$type->isBoolean()->yes()) {
            return null;
        }
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
