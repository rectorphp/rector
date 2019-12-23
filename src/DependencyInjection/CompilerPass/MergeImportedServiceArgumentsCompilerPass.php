<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\CompilerPass;

use Rector\Contract\Rector\RectorInterface;
use Rector\DependencyInjection\Collector\CachedServiceArgumentCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class MergeImportedServiceArgumentsCompilerPass implements CompilerPassInterface
{
    /**
     * @var CachedServiceArgumentCollector
     */
    private $cachedServiceArgumentCollector;

    public function __construct(CachedServiceArgumentCollector $cachedServiceArgumentCollector)
    {
        $this->cachedServiceArgumentCollector = $cachedServiceArgumentCollector;
    }

    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            $this->completeCollectedArguments($id, $definition);
        }
    }

    private function completeCollectedArguments(string $serviceClass, Definition $definition): void
    {
        if (! is_a($serviceClass, RectorInterface::class, true)) {
            return;
        }

        $cachedServiceKeyArguments = $this->cachedServiceArgumentCollector->getClassArgumentValue($serviceClass);
        if ($cachedServiceKeyArguments === []) {
            return;
        }

        // prevent argument override
        foreach ($cachedServiceKeyArguments as $argumentName => $argumentValue) {
            $definition->setArgument($argumentName, $argumentValue);
        }
    }
}
