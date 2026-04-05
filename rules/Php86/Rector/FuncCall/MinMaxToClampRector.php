<?php

declare (strict_types=1);
namespace Rector\Php86\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php86\Rector\FuncCall\MinMaxToClampRector\MinMaxToClampRectorTest
 */
final class MinMaxToClampRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert nested min()/max() calls to clamp()', [new CodeSample(<<<'CODE_SAMPLE'
$result = max(0, min(100, $value));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$result = clamp($value, 0, 100);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($this->isName($node, 'max')) {
            return $this->matchClampFuncCall($node, 'min');
        }
        if ($this->isName($node, 'min')) {
            return $this->matchClampFuncCall($node, 'max');
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::CLAMP;
    }
    private function matchClampFuncCall(FuncCall $outerFuncCall, string $expectedInnerFuncName): ?FuncCall
    {
        $args = $outerFuncCall->getArgs();
        if (count($args) !== 2) {
            return null;
        }
        if (!$this->isSupportedArg($args[0]) || !$this->isSupportedArg($args[1])) {
            return null;
        }
        $leftValue = $args[0]->value;
        $rightValue = $args[1]->value;
        if ($leftValue instanceof FuncCall) {
            return $this->createClampFuncCall($outerFuncCall, $leftValue, $rightValue, $expectedInnerFuncName);
        }
        if ($rightValue instanceof FuncCall) {
            return $this->createClampFuncCall($outerFuncCall, $rightValue, $leftValue, $expectedInnerFuncName);
        }
        return null;
    }
    private function createClampFuncCall(FuncCall $outerFuncCall, FuncCall $innerFuncCall, Expr $outerBoundExpr, string $expectedInnerFuncName): ?FuncCall
    {
        if ($innerFuncCall->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($innerFuncCall, $expectedInnerFuncName)) {
            return null;
        }
        $args = $innerFuncCall->getArgs();
        if (count($args) !== 2) {
            return null;
        }
        if (!$this->isSupportedArg($args[0]) || !$this->isSupportedArg($args[1])) {
            return null;
        }
        $valueAndBound = $this->matchValueAndKnownBound($args[0]->value, $args[1]->value);
        if ($valueAndBound === null) {
            return null;
        }
        [$valueExpr, $innerBoundExpr] = $valueAndBound;
        if ($this->isName($outerFuncCall, 'max')) {
            return $this->nodeFactory->createFuncCall('clamp', [$valueExpr, $outerBoundExpr, $innerBoundExpr]);
        }
        return $this->nodeFactory->createFuncCall('clamp', [$valueExpr, $innerBoundExpr, $outerBoundExpr]);
    }
    private function isSupportedArg(Arg $arg): bool
    {
        return !$arg->unpack && !$arg->name instanceof Identifier;
    }
    /**
     * @return array{Expr, Expr}|null
     */
    private function matchValueAndKnownBound(Expr $firstExpr, Expr $secondExpr): ?array
    {
        $isFirstKnownBound = $this->isKnownBound($firstExpr);
        $isSecondKnownBound = $this->isKnownBound($secondExpr);
        if ($isFirstKnownBound === $isSecondKnownBound) {
            return null;
        }
        if ($isFirstKnownBound) {
            return [$secondExpr, $firstExpr];
        }
        return [$firstExpr, $secondExpr];
    }
    private function isKnownBound(Expr $expr): bool
    {
        return $this->getNativeType($expr)->isConstantScalarValue()->yes();
    }
}
