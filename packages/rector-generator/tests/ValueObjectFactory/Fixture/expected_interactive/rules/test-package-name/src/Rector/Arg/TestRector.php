<?php

declare(strict_types=1);

namespace Rector\TestPackageName\Rector\Arg;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**

 * @see \Rector\TestPackageName\Tests\Rector\Arg\TestRector\TestRectorTest
 */
final class TestRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Description', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->something();
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->somethingElse();
    }
}
CODE_SAMPLE

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Arg::class];
    }

    /**
     * @param \PhpParser\Node\Arg $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
