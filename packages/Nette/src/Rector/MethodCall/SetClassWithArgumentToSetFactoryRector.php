<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\MethodCall;

use Nette\DI\Definitions\ServiceDefinition;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/nette/di/pull/146/files
 *
 * @see \Rector\Nette\Tests\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector\SetClassWithArgumentToSetFactoryRectorTest
 */
final class SetClassWithArgumentToSetFactoryRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change setClass with class and arguments to separated methods', [
            new CodeSample(
                <<<'PHP'
use Nette\DI\ContainerBuilder;

class SomeClass
{
    public function run(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addDefinition('...')
            ->setClass('SomeClass', [1, 2]);
    }
}
PHP
,
                <<<'PHP'
use Nette\DI\ContainerBuilder;

class SomeClass
{
    public function run(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addDefinition('...')
            ->setFactory('SomeClass', [1, 2]);
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'setClass')) {
            return null;
        }

        if (count($node->args) !== 2) {
            return null;
        }

        if (! $this->isObjectType($node->var, ServiceDefinition::class)) {
            return null;
        }

        $node->name = new Identifier('setFactory');

        return $node;
    }
}
