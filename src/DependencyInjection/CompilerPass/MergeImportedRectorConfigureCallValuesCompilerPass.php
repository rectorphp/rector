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
        $getConfigureCallValues = $this->configureCallValuesCollector->getConfigureCallValues($serviceClass);

        $configureCallValues = $definition->hasMethodCall(self::CONFIGURE_METHOD_NAME) && $getConfigureCallValues === []
            ? $this->mergeConfigure($definition->getMethodCalls())
            : $getConfigureCallValues;

        if ($configureCallValues === []) {
            return;
        }

        // The following code only removes the first method call named `configure`,
        // and leaves the rest. It's a bug, see https://github.com/symfony/symfony/pull/40167
        // $definition->removeMethodCall(self::CONFIGURE_METHOD_NAME);
        $definition->setMethodCalls(array_filter($definition->getMethodCalls(), function(array $call) {
            return $call[0] !== self::CONFIGURE_METHOD_NAME;
        }));

        $definition->addMethodCall(self::CONFIGURE_METHOD_NAME, [$configureCallValues]);
    }

    /**
     * @param array<int, array<int, mixed>> $configuration
     * @return mixed[]
     */
    private function mergeConfigure(array $configuration): array
    {
        $mergedConfigure = [];

        foreach ($configuration as $configure) {
            if (isset($configure[0]) && $configure[0] !== 'configure') {
                continue;
            }

            if (! isset($configure[1])) {
                continue;
            }

            foreach ($configure[1] as $configureMethodCall) {
                $mergedConfigure = array_merge_recursive($mergedConfigure, $configureMethodCall);
            }
        }

        return $mergedConfigure;
    }
}
