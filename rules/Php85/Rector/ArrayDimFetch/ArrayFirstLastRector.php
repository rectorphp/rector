<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\Int_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://php.watch/versions/8.5/array_first-array_last
 * @see \Rector\Tests\Php85\Rector\ArrayDimFetch\ArrayFirstLastRector\ArrayFirstLastRectorTest
 */
final class ArrayFirstLastRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const ARRAY_KEY_FIRST = 'array_key_first';
    /**
     * @var string
     */
    private const ARRAY_KEY_LAST = 'array_key_last';
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make use of array_first() and array_last()', [new CodeSample(<<<'CODE_SAMPLE'
echo $array[array_key_first($array)];
echo $array[array_key_last($array)];
echo array_values($array)[0];
echo array_values($array)[count($array) - 1];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo array_first($array);
echo array_last($array);
echo array_first($array);
echo array_last($array);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node): ?FuncCall
    {
        if ($node->dim instanceof FuncCall) {
            return $this->refactorArrayKeyPattern($node);
        }
        if ($node->var instanceof FuncCall && ($node->dim instanceof Int_ || $node->dim instanceof Minus)) {
            return $this->refactorArrayValuesPattern($node);
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ARRAY_FIRST_LAST;
    }
    private function refactorArrayKeyPattern(ArrayDimFetch $arrayDimFetch): ?FuncCall
    {
        if (!$arrayDimFetch->dim instanceof FuncCall) {
            return null;
        }
        if (!$this->isNames($arrayDimFetch->dim, [self::ARRAY_KEY_FIRST, self::ARRAY_KEY_LAST])) {
            return null;
        }
        if ($arrayDimFetch->dim->isFirstClassCallable()) {
            return null;
        }
        if (count($arrayDimFetch->dim->getArgs()) !== 1) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($arrayDimFetch->var, $arrayDimFetch->dim->getArgs()[0]->value)) {
            return null;
        }
        if ($this->shouldSkip($arrayDimFetch, $arrayDimFetch->var)) {
            return null;
        }
        $functionName = $this->isName($arrayDimFetch->dim, self::ARRAY_KEY_FIRST) ? 'array_first' : 'array_last';
        return $this->nodeFactory->createFuncCall($functionName, [$arrayDimFetch->var]);
    }
    private function refactorArrayValuesPattern(ArrayDimFetch $arrayDimFetch): ?FuncCall
    {
        if (!$arrayDimFetch->var instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($arrayDimFetch->var, 'array_values')) {
            return null;
        }
        if ($arrayDimFetch->var->isFirstClassCallable()) {
            return null;
        }
        if (count($arrayDimFetch->var->getArgs()) !== 1) {
            return null;
        }
        if ($this->shouldSkip($arrayDimFetch, $arrayDimFetch)) {
            return null;
        }
        $arrayArg = $arrayDimFetch->var->getArgs()[0]->value;
        if ($arrayDimFetch->dim instanceof Int_ && $arrayDimFetch->dim->value === 0) {
            return $this->nodeFactory->createFuncCall('array_first', [$arrayArg]);
        }
        if ($arrayDimFetch->dim instanceof Minus) {
            if (!$arrayDimFetch->dim->left instanceof FuncCall) {
                return null;
            }
            if (!$this->isName($arrayDimFetch->dim->left, 'count')) {
                return null;
            }
            if ($arrayDimFetch->dim->left->isFirstClassCallable()) {
                return null;
            }
            if (count($arrayDimFetch->dim->left->getArgs()) !== 1) {
                return null;
            }
            if (!$arrayDimFetch->dim->right instanceof Int_ || $arrayDimFetch->dim->right->value !== 1) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($arrayArg, $arrayDimFetch->dim->left->getArgs()[0]->value)) {
                return null;
            }
            return $this->nodeFactory->createFuncCall('array_last', [$arrayArg]);
        }
        return null;
    }
    private function shouldSkip(ArrayDimFetch $arrayDimFetch, Node $scopeNode): bool
    {
        $scope = ScopeFetcher::fetch($scopeNode);
        if ($scope->isInExpressionAssign($arrayDimFetch)) {
            return \true;
        }
        if ($arrayDimFetch->getAttribute(AttributeKey::IS_BEING_ASSIGNED) === \true) {
            return \true;
        }
        if ($arrayDimFetch->getAttribute(AttributeKey::IS_ASSIGN_OP_VAR) === \true) {
            return \true;
        }
        return (bool) $arrayDimFetch->getAttribute(AttributeKey::IS_UNSET_VAR);
    }
}
