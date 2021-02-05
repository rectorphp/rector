<?php

declare(strict_types=1);

namespace Rector\LaminasServiceManager4\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\LaminasServiceManager4\Tests\Rector\MethodCall\RemoveServiceLocatorRector\RemoveServiceLocatorRectorTest
 */
final class RemoveServiceLocatorRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const OBJECT_TYPES = [
        'Laminas\ServiceManager\ServiceLocatorInterface',
        'Zend\ServiceManager\ServiceLocatorInterface',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove getServiceLocator() method call to use its variable instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Laminas\ServiceManager\ServiceLocatorInterface;

class SomeFactory extends FactoryInterface
{
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator->getServiceLocator();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Laminas\ServiceManager\ServiceLocatorInterface;

class SomeFactory extends FactoryInterface
{
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator;
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
        if (! $this->isObjectTypes($node->var, self::OBJECT_TYPES)) {
            return null;
        }

        if (! $this->isName($node->name, 'getServiceLocator')) {
            return null;
        }

        return $node->var;
    }
}
