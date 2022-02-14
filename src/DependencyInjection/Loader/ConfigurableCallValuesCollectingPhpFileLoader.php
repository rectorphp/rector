<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection\Loader;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @property-read ContainerBuilder $container
 */
final class ConfigurableCallValuesCollectingPhpFileLoader extends PhpFileLoader
{
    public function __construct(
        ContainerBuilder $containerBuilder,
        FileLocatorInterface $fileLocator,
        private readonly ConfigureCallValuesCollector $configureCallValuesCollector
    ) {
        parent::__construct($containerBuilder, $fileLocator);
    }

    public function load(mixed $resource, string $type = null): mixed
    {
        // this call collects root values
        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();

        parent::load($resource, $type);

        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();

        return null;
    }

    public function import(
        mixed $resource,
        string $type = null,
        bool|string $ignoreErrors = false,
        string $sourceResource = null,
        $exclude = null
    ): mixed {
        // this call collects root values
        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();

        parent::import($resource, $type, $ignoreErrors, $sourceResource, $exclude);

        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();

        return null;
    }

    private function collectConfigureCallsFromJustImportedConfigurableRectorDefinitions(): void
    {
        foreach ($this->container->getDefinitions() as $class => $definition) {
            if (! is_a($class, ConfigurableRectorInterface::class, true)) {
                continue;
            }

            $this->configureCallValuesCollector->collectFromServiceAndClassName($class, $definition);
        }
    }
}
