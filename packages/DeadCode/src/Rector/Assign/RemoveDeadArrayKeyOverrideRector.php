<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/kscUu - it has to keep order of keys
 */

/**
 * @see \Rector\DeadCode\Tests\Rector\Assign\RemoveDeadArrayKeyOverrideRector\RemoveDeadArrayKeyOverrideRectorTest
 */
final class RemoveDeadArrayKeyOverrideRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove overridden assign of duplicated array key', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $values[1] = '1';
        $values[2] = '2';
        $values[1] = '3';
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $values[1] = '3';
        $values[2] = '2';
    }
}
PHP

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }

    /**
     * @param \PhpParser\Node\Expr\Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
