<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Symfony\Rector\FrameworkBundle\AbstractToConstructorInjectionRector;

/**
 * @see \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\ContextGetByTypeToConstructorInjectionRectorTest
 */
final class ContextGetByTypeToConstructorInjectionRector extends AbstractToConstructorInjectionRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Move dependency passed to all children to parent as @inject/@required dependency',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function run()
    {
        $someTypeToInject = $this->context->getByType(SomeTypeToInject::class);
    }
}
PHP
,
                    <<<'PHP'
class SomeClass
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @var SomeTypeToInject
     */
    private $someTypeToInject;

    public function __construct(SomeTypeToInject $someTypeToInject)
    {
        $this->someTypeToInject = $someTypeToInject;
    }

    public function run()
    {
        $someTypeToInject = $this->someTypeToInject;
    }
}
PHP

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
        if (! $node->var instanceof PropertyFetch) {
            return null;
        }

        if (! $this->isObjectType($node->var, 'Nette\DI\Container')) {
            return null;
        }

        if (! $this->isName($node->name, 'getByType')) {
            return null;
        }

        return $this->processMethodCallNode($node);
    }
}
