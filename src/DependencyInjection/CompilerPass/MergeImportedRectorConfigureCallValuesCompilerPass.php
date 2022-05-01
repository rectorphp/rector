<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\CompilerPass;

use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition;
final class MergeImportedRectorConfigureCallValuesCompilerPass implements \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @var string
     */
    private const CONFIGURE_METHOD_NAME = 'configure';
    /**
     * @readonly
     * @var \Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector
     */
    private $configureCallValuesCollector;
    public function __construct(\Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector $configureCallValuesCollector)
    {
        $this->configureCallValuesCollector = $configureCallValuesCollector;
    }
    public function process(\RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            $this->completeCollectedArguments($id, $definition);
        }
    }
    private function completeCollectedArguments(string $serviceClass, \RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition $definition) : void
    {
        $configureCallValues = $this->configureCallValuesCollector->getConfigureCallValues($serviceClass);
        if ($configureCallValues === []) {
            return;
        }
        $definition->removeMethodCall(self::CONFIGURE_METHOD_NAME);
        $definition->addMethodCall(self::CONFIGURE_METHOD_NAME, [$configureCallValues]);
    }
}
