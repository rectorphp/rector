<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/functions.arrow.php
 *
 * @see \Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\DowngradeStripTagsCallWithArrayRectorTest
 */
final class DowngradeStripTagsCallWithArrayRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert 2nd param to `strip_tags` from array to string', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($string)
    {
        // Strings: do no change
        strip_tags($string, '<a><p>');

        // Arrays: change to string
        strip_tags($string, ['a', 'p']);

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($string)
    {
        // Strings: do no change
        strip_tags($string, '<a><p>');

        // Arrays: change to string
        strip_tags($string, '<a><p>');

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);
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
        return null;
    }
}
