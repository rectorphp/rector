<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector\ContainerBuilderCompileEnvArgumentRectorTest
 */
final class ContainerBuilderCompileEnvArgumentRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns old default value to parameter in ContainerBuilder->build() method in DI in Symfony',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->compile();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->compile(true);
CODE_SAMPLE
                ),
            ]
        );
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
        if (! $this->isObjectType($node, 'Symfony\Component\DependencyInjection\ContainerBuilder')) {
            return null;
        }

        if (! $this->isName($node->name, 'compile')) {
            return null;
        }

        if (count($node->args) === 1) {
            return null;
        }

        $node->args = $this->createArgs([$this->createTrue()]);

        return $node;
    }
}
