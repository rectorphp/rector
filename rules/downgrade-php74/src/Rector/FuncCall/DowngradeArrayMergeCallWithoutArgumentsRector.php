<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\DowngradeArrayMergeCallWithoutArgumentsRectorTest
 */
final class DowngradeArrayMergeCallWithoutArgumentsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add missing param to `array_merge` and `array_merge_recursive`', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        array_merge();
        array_merge_recursive();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        array_merge([]);
        array_merge_recursive([]);
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->shouldRefactor($node)) {
            return null;
        }

        $node->args = [new Arg(new Array_())];

        return $node;
    }

    private function shouldRefactor(FuncCall $funcCall): bool
    {
        if (! $this->isNames($funcCall, ['array_merge', 'array_merge_recursive'])) {
            return false;
        }

        // If param is provided, do nothing
        if ($funcCall->args !== []) {
            return false;
        }

        return true;
    }
}
