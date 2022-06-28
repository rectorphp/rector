<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\String_;

use PhpParser\Node;
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
final class DowngradeNegativeStringOffsetToStrlenRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade negative string offset to strlen', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class, String_::class, Variable::class, PropertyFetch::class, StaticPropertyFetch::class];
    }
    /**
     * @param FuncCall|String_|Variable|PropertyFetch|StaticPropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof FuncCall) {
            return $this->processForFuncCall($node);
        }
        return $this->processForStringOrVariableOrProperty($node);
    }
    /**
     * @param \PhpParser\Node\Scalar\String_|\PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $expr
     */
    private function processForStringOrVariableOrProperty($expr) : ?Expr
    {
        $nextNode = $expr->getAttribute(AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof UnaryMinus) {
            return null;
        }
        $parentOfNextNode = $nextNode->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentOfNextNode instanceof ArrayDimFetch) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($parentOfNextNode->dim, $nextNode)) {
            return null;
        }
        /** @var UnaryMinus $dim */
        $dim = $parentOfNextNode->dim;
        $strlenFuncCall = $this->nodeFactory->createFuncCall('strlen', [$expr]);
        $parentOfNextNode->dim = new Minus($strlenFuncCall, $dim->expr);
        return $expr;
    }
    private function processForFuncCall(FuncCall $funcCall) : ?FuncCall
    {
        $name = $this->getName($funcCall);
        if ($name !== 'strpos') {
            return null;
        }
        $args = $funcCall->getArgs();
        if (!isset($args[2])) {
            return null;
        }
        $thirdArg = $args[2];
        if (!$thirdArg->value instanceof UnaryMinus) {
            return null;
        }
        $strlenFuncCall = $this->nodeFactory->createFuncCall('strlen', [$args[0]]);
        $thirdArg->value = new Minus($strlenFuncCall, $thirdArg->value->expr);
        return $funcCall;
    }
}
