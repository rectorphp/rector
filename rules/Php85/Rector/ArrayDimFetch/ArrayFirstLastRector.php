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
    private function refactorArrayKeyPattern(ArrayDimFetch $node): ?FuncCall
    {
        if (!$node->dim instanceof FuncCall) {
            return null;
        }
        if (!$this->isNames($node->dim, [self::ARRAY_KEY_FIRST, self::ARRAY_KEY_LAST])) {
            return null;
        }
        if ($node->dim->isFirstClassCallable()) {
            return null;
        }
        if (count($node->dim->getArgs()) !== 1) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($node->var, $node->dim->getArgs()[0]->value)) {
            return null;
        }
        if ($this->shouldSkip($node, $node->var)) {
            return null;
        }
        $functionName = $this->isName($node->dim, self::ARRAY_KEY_FIRST) ? 'array_first' : 'array_last';
        return $this->nodeFactory->createFuncCall($functionName, [$node->var]);
    }
    private function refactorArrayValuesPattern(ArrayDimFetch $node): ?FuncCall
    {
        if (!$node->var instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($node->var, 'array_values')) {
            return null;
        }
        if ($node->var->isFirstClassCallable()) {
            return null;
        }
        if (count($node->var->getArgs()) !== 1) {
            return null;
        }
        if ($this->shouldSkip($node, $node)) {
            return null;
        }
        $arrayArg = $node->var->getArgs()[0]->value;
        if ($node->dim instanceof Int_ && $node->dim->value === 0) {
            return $this->nodeFactory->createFuncCall('array_first', [$arrayArg]);
        }
        if ($node->dim instanceof Minus) {
            if (!$node->dim->left instanceof FuncCall) {
                return null;
            }
            if (!$this->isName($node->dim->left, 'count')) {
                return null;
            }
            if ($node->dim->left->isFirstClassCallable()) {
                return null;
            }
            if (count($node->dim->left->getArgs()) !== 1) {
                return null;
            }
            if (!$node->dim->right instanceof Int_ || $node->dim->right->value !== 1) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($arrayArg, $node->dim->left->getArgs()[0]->value)) {
                return null;
            }
            return $this->nodeFactory->createFuncCall('array_last', [$arrayArg]);
        }
        return null;
    }
    private function shouldSkip(ArrayDimFetch $node, Node $scopeNode): bool
    {
        $scope = ScopeFetcher::fetch($scopeNode);
        if ($scope->isInExpressionAssign($node)) {
            return \true;
        }
        if ($node->getAttribute(AttributeKey::IS_UNSET_VAR)) {
            return \true;
        }
        return \false;
    }
}
