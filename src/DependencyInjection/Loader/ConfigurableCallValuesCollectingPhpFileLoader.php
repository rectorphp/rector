<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\Loader;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use RectorPrefix20220501\Symfony\Component\Config\FileLocatorInterface;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
/**
 * @property-read ContainerBuilder $container
 */
final class ConfigurableCallValuesCollectingPhpFileLoader extends \RectorPrefix20220501\Symfony\Component\DependencyInjection\Loader\PhpFileLoader
{
    /**
     * @readonly
     * @var \Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector
     */
    private $configureCallValuesCollector;
    public function __construct(\RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, \RectorPrefix20220501\Symfony\Component\Config\FileLocatorInterface $fileLocator, \Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector $configureCallValuesCollector)
    {
        $this->configureCallValuesCollector = $configureCallValuesCollector;
        parent::__construct($containerBuilder, $fileLocator);
    }
    /**
     * @param mixed $resource
     * @return mixed
     */
    public function load($resource, string $type = null)
    {
        // this call collects root values
        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();
        parent::load($resource, $type);
        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();
        return null;
    }
    /**
     * @param bool|string $ignoreErrors
     * @param mixed $resource
     * @return mixed
     */
    public function import($resource, string $type = null, $ignoreErrors = \false, string $sourceResource = null, $exclude = null)
    {
        // this call collects root values
        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();
        parent::import($resource, $type, $ignoreErrors, $sourceResource, $exclude);
        $this->collectConfigureCallsFromJustImportedConfigurableRectorDefinitions();
        return null;
    }
    private function collectConfigureCallsFromJustImportedConfigurableRectorDefinitions() : void
    {
        foreach ($this->container->getDefinitions() as $class => $definition) {
            if (!\is_a($class, \Rector\Core\Contract\Rector\ConfigurableRectorInterface::class, \true)) {
                continue;
            }
            $this->configureCallValuesCollector->collectFromServiceAndClassName($class, $definition);
        }
    }
}
