<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection\CompilerPass;

use Rector\Core\DependencyInjection\Collector\ConfigurableRectorConfigureCallValuesCollector;
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
     * @var ConfigurableRectorConfigureCallValuesCollector
     */
    private $configurableRectorConfigureCallValuesCollector;

    public function __construct(
        ConfigurableRectorConfigureCallValuesCollector $configurableRectorConfigureCallValuesCollector
    ) {
        $this->configurableRectorConfigureCallValuesCollector = $configurableRectorConfigureCallValuesCollector;
    }

    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            $this->completeCollectedArguments($id, $definition);
        }
    }

    private function completeCollectedArguments(string $serviceClass, Definition $definition): void
    {
        $configureCallValues = $this->configurableRectorConfigureCallValuesCollector->getConfigureCallValues(
            $serviceClass
        );
        if ($configureCallValues === []) {
            return;
        }

        $definition->removeMethodCall(self::CONFIGURE_METHOD_NAME);
        $definition->addMethodCall(self::CONFIGURE_METHOD_NAME, [$configureCallValues]);
    }
}
