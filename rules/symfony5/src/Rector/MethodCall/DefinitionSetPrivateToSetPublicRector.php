<?php

declare(strict_types=1);

namespace Rector\Symfony5\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#dependencyinjection
 * @see \Rector\Symfony5\Tests\Rector\MethodCall\DefinitionSetPrivateToSetPublicRector\DefinitionSetPrivateToSetPublicRectorTest
 */
final class DefinitionSetPrivateToSetPublicRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrates from deprecated setPrivate() to setPublic()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Definition;

class SomeClass
{
    public function run(Definition $definition)
    {
        $definition->setPrivate(true);
        $definition->setPrivate(false);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Definition;

class SomeClass
{
    public function run(Definition $definition)
    {
        $definition->setPublic(false);
        $definition->setPublic(true);
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        return $node;
    }
}
