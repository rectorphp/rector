<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Process;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @see \Rector\Symfony\Tests\Rector\Process\ProcessBuilderInstanceRector\ProcessBuilderInstanceRectorTest
 */
final class ProcessBuilderInstanceRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.',
            [
                new CodeSample(
                    '$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);',
                    '$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);'
                ),
            ]
        );
    }

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
        if (! $this->isName($node->class, ProcessBuilder::class)) {
            return null;
        }

        if (! $this->isName($node->name, 'create')) {
            return null;
        }

        return new New_($node->class, $node->args);
    }
}
