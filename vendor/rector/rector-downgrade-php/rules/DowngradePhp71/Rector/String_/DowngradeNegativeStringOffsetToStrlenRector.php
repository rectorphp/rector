<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
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

echo strpos($value, 'b', -3);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo substr('abcdef', -2, 1);

echo strpos($value, 'b', strlen($value) - 3);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class, ArrayDimFetch::class];
    }
    /**
     * @param FuncCall|ArrayDimFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ArrayDimFetch) {
            return $this->refactorArrayDimFetch($node);
        }
        return $this->processForFuncCall($node);
    }
    private function processForFuncCall(FuncCall $funcCall) : ?FuncCall
    {
        if (!$this->isName($funcCall, 'strpos')) {
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
    private function refactorArrayDimFetch(ArrayDimFetch $arrayDimFetch) : ?FuncCall
    {
        if (!$arrayDimFetch->dim instanceof UnaryMinus) {
            return null;
        }
        $unaryMinus = $arrayDimFetch->dim;
        if (!$unaryMinus->expr instanceof LNumber) {
            return null;
        }
        $lNumber = $unaryMinus->expr;
        return $this->nodeFactory->createFuncCall('substr', [$arrayDimFetch->var, new LNumber(-$lNumber->value), new LNumber(1)]);
    }
}
