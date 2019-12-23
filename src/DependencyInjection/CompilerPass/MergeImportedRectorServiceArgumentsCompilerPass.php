<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\CompilerPass;

use Rector\DependencyInjection\Collector\RectorServiceArgumentCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class MergeImportedRectorServiceArgumentsCompilerPass implements CompilerPassInterface
{
    /**
     * @var RectorServiceArgumentCollector
     */
    private $rectorServiceArgumentCollector;

    public function __construct(RectorServiceArgumentCollector $rectorServiceArgumentCollector)
    {
        $this->rectorServiceArgumentCollector = $rectorServiceArgumentCollector;
    }

    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            $this->completeCollectedArguments($id, $definition);
        }
    }

    private function completeCollectedArguments(string $serviceClass, Definition $definition): void
    {
        $cachedServiceKeyArguments = $this->rectorServiceArgumentCollector->getClassArgumentValue($serviceClass);
        if ($cachedServiceKeyArguments === []) {
            return;
        }

        // prevent argument override
        foreach ($cachedServiceKeyArguments as $argumentName => $argumentValue) {
            $definition->setArgument($argumentName, $argumentValue);
        }
    }
}
