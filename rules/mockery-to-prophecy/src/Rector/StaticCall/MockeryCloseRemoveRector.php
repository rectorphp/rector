<?php

declare(strict_types=1);

namespace Rector\MockeryToProphecy\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\MockeryToProphecy\Tests\Rector\StaticCall\MockeryToProphecyRector\MockeryToProphecyRectorTest
 */
final class MockeryCloseRemoveRector extends AbstractPHPUnitRector
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isStaticCallNamed($node, 'Mockery', 'close')) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes mockery close from test classes',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
public function tearDown() : void
{
    \Mockery::close();
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
public function tearDown() : void
{
}
CODE_SAMPLE
                ),
            ]
        );
    }
}
