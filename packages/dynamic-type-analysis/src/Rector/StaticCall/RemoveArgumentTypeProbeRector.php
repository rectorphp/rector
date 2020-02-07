<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;

/**
 * @see \Rector\DynamicTypeAnalysis\Tests\Rector\StaticCall\RemoveArgumentTypeProbeRector\RemoveArgumentTypeProbeRectorTest
 */
final class RemoveArgumentTypeProbeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Clean up probe that records argument types', [
            new CodeSample(
                <<<'PHP'
use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;

class SomeClass
{
    public function run($arg)
    {
        TypeStaticProbe::recordArgumentType($arg, __METHOD__, 0);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($arg)
    {
    }
}
PHP

            ),
        ]);
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
        if (! $this->isObjectType($node->class, TypeStaticProbe::class)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}
