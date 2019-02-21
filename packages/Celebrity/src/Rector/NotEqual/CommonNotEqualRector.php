<?php declare(strict_types=1);

namespace Rector\Celebrity\Rector\NotEqual;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/a/4294663/1348344
 */
final class CommonNotEqualRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use common != instead of less known <> with same meaning', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one, $two)
    {
        return $one <> $two;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one, $two)
    {
        return $one != $two;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [NotEqual::class];
    }

    /**
     * @param NotEqual $node
     */
    public function refactor(Node $node): ?Node
    {
        // invoke override to default "!="
        $node->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $node;
    }
}
