<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection\CompilerPass;

use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class MergeImportedRectorConfigureCallValuesCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const CONFIGURE_METHOD_NAME = 'configure';

    /**
     * @var ConfigureCallValuesCollector
     */
    private $configureCallValuesCollector;

    public function __construct(ConfigureCallValuesCollector $configureCallValuesCollector)
    {
        $this->configureCallValuesCollector = $configureCallValuesCollector;
    }

    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            $this->completeCollectedArguments($id, $definition);
        }
    }

    private function completeCollectedArguments(string $serviceClass, Definition $definition): void
    {
        $configureCallValues = $definition->hasMethodCall(self::CONFIGURE_METHOD_NAME)
            ? $this->mergeConfigure($definition->getMethodCalls())
            : $this->configureCallValuesCollector->getConfigureCallValues($serviceClass);

        if ($configureCallValues === []) {
            return;
        }

        $definition->removeMethodCall(self::CONFIGURE_METHOD_NAME);
        $definition->addMethodCall(self::CONFIGURE_METHOD_NAME, [$configureCallValues]);
    }

    /**
     * @return mixed[]
     */
    private function mergeConfigure(array $configuration): array
    {
        $mergedConfigure = [];

        foreach ($configuration as $configure) {
            $mergedConfigure = array_merge_recursive($mergedConfigure, $configure[1][0]);
        }

        return $mergedConfigure;
    }
}
