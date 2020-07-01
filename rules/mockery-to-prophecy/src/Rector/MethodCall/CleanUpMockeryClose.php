<?php

declare(strict_types=1);

namespace Rector\MockeryToProphecy\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MockeryToProphecy\MockeryUtils;

final class CleanUpMockeryClose extends AbstractPHPUnitRector
{
    use MockeryUtils;

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$this->isInTestClass($node)) {
            return null;
        }

        if ($this->isCallToMockery($node) && $this->isName($node->name, 'close')) {
            $this->removeNode($node);
        }

        return null;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes mockery close from test classes',
            [
                new CodeSample(
                    'public function tearDown() : void
    {
        \Mockery::close();
    }',
                    '    public function tearDown() : void
    {
    }'
                )
            ]
        );
    }
}
