<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\FuncCall;
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make use of array_first() and array_last()', [new CodeSample(<<<'CODE_SAMPLE'
echo $array[array_key_first($array)];
echo $array[array_key_last($array)];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo array_first($array);
echo array_last($array;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node) : ?FuncCall
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
        if (\count($node->dim->getArgs()) !== 1) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($node->var, $node->dim->getArgs()[0]->value)) {
            return null;
        }
        $functionName = $this->isName($node->dim, self::ARRAY_KEY_FIRST) ? 'array_first' : 'array_last';
        return $this->nodeFactory->createFuncCall($functionName, [$node->var]);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ARRAY_FIRST_LAST;
    }
}
