<?php

declare(strict_types=1);

namespace Rector\ModeratePackage\Rector\MethodCall;

use PhpParser\Node;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**

 * @see \Rector\ModeratePackage\Tests\Rector\MethodCall\WhateverRector\WhateverRectorTest
 */
final class WhateverRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
 * @var string
 */
public const CLASS_TYPE_TO_METHOD_NAME = 'class_type_to_method_name';

    /**
 * @var mixed[]
 */
private $classTypeToMethodName = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change $service->arg(...) to $service->call(...)', [
            new ConfiguredCodeSample(
                <<<'PHP'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->arg('$key', 'value');
}
PHP
,
                <<<'PHP'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->call('configure', [[
            '$key' => 'value'
        ]]);
}
PHP
,
                [self::CLASS_TYPE_TO_METHOD_NAME => ['SomeClass' => 'configure']]
            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }

    /**
     * @param \PhpParser\Node\Expr\MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }

    /**
 * @param mixed[] $configuration
 */
public function configure(array $configuration): void
{
    $this->classTypeToMethodName = $configuration[self::CLASS_TYPE_TO_METHOD_NAME] ?? [];
}
}
