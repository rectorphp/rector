<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection\Loader;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\DependencyInjection\Collector\ConfigurableRectorConfigureCallValuesCollector;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class ConfigurableCallValuesCollectingPhpFileLoader extends PhpFileLoader
{
    /**
     * @var ConfigurableRectorConfigureCallValuesCollector
     */
    private $configurableRectorConfigureCallValuesCollector;

    public function __construct(
        ContainerBuilder $containerBuilder,
        FileLocatorInterface $fileLocator,
        ConfigurableRectorConfigureCallValuesCollector $configurableRectorConfigureCallValuesCollector
    ) {
        $this->configurableRectorConfigureCallValuesCollector = $configurableRectorConfigureCallValuesCollector;

        parent::__construct($containerBuilder, $fileLocator);
    }

    public function import(
        $resource,
        $type = null,
        $ignoreErrors = false,
        $sourceResource = null,
        $exclude = null
    ): void {
        // this call collects root values
        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();

        parent::import($resource, $type, $ignoreErrors, $sourceResource, $exclude);

        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();
    }

    private function collectConfigureCallsFromJustImportedConfigurableRectorDefinitions(): void
    {
        foreach ($this->container->getDefinitions() as $class => $definition) {
            /** @var string $class */
            if (! is_a($class, ConfigurableRectorInterface::class, true)) {
                continue;
            }

            $this->configurableRectorConfigureCallValuesCollector->collectFromServiceAndClassName($class, $definition);
        }
    }
}
