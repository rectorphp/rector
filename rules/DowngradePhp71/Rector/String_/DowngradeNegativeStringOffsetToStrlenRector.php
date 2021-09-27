<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\DowngradeNegativeStringOffsetToStrlenRectorTest
 */
final class DowngradeNegativeStringOffsetToStrlenRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade negative string offset to strlen', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
echo 'abcdef'[-2];
echo strpos('aabbcc', 'b', -3);
echo strpos($var, 'b', -3);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo 'abcdef'[strlen('abcdef') - 2];
echo strpos('aabbcc', 'b', strlen('aabbcc') - 3);
echo strpos($var, 'b', strlen($var) - 3);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Scalar\String_::class, \PhpParser\Node\Expr\Variable::class, \PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\StaticPropertyFetch::class];
    }
    /**
     * @param FuncCall|String_|Variable|PropertyFetch|StaticPropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->processForFuncCall($node);
        }
        return $this->processForStringOrVariableOrProperty($node);
    }
    /**
     * @param \PhpParser\Node\Scalar\String_|\PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $expr
     */
    private function processForStringOrVariableOrProperty($expr) : ?\PhpParser\Node\Expr
    {
        $nextNode = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node\Expr\UnaryMinus) {
            return null;
        }
        $parentOfNextNode = $nextNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentOfNextNode instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($parentOfNextNode->dim, $nextNode)) {
            return null;
        }
        /** @var UnaryMinus $dim */
        $dim = $parentOfNextNode->dim;
        $strlenFuncCall = $this->nodeFactory->createFuncCall('strlen', [$expr]);
        $parentOfNextNode->dim = new \PhpParser\Node\Expr\BinaryOp\Minus($strlenFuncCall, $dim->expr);
        return $expr;
    }
    private function processForFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\FuncCall
    {
        $name = $this->getName($funcCall);
        if ($name !== 'strpos') {
            return null;
        }
        $args = $funcCall->args;
        if (!isset($args[2])) {
            return null;
        }
        if ($args[2] instanceof \PhpParser\Node\Arg && !$args[2]->value instanceof \PhpParser\Node\Expr\UnaryMinus) {
            return null;
        }
        if (!$args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if (!$funcCall->args[2] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $strlenFuncCall = $this->nodeFactory->createFuncCall('strlen', [$args[0]]);
        $funcCall->args[2]->value = new \PhpParser\Node\Expr\BinaryOp\Minus($strlenFuncCall, $funcCall->args[2]->value->expr);
        return $funcCall;
    }
}
