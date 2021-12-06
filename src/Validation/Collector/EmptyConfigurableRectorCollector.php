<?php

declare(strict_types=1);

namespace Rector\Core\Validation\Collector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @see \Rector\Core\Tests\Validation\Collector\EmptyConfigurableRectorCollector\EmptyConfigurableRectorCollectorTest
 */
final class EmptyConfigurableRectorCollector
{
    public function __construct(
        private readonly ContainerBuilder $containerBuilder
    ) {
    }

    /**
     * @return array<class-string<ConfigurableRectorInterface>>
     */
    public function resolveEmptyConfigurableRectorClasses(): array
    {
        $emptyConfigurableRectorClasses = [];

        foreach ($this->containerBuilder->getServiceIds() as $serviceId) {
            if (! is_a($serviceId, ConfigurableRectorInterface::class, true)) {
                continue;
            }

            // it seems always loaded
            if (is_a($serviceId, RenameClassNonPhpRector::class, true)) {
                continue;
            }

            $serviceDefinition = $this->containerBuilder->getDefinition($serviceId);
            if ($this->hasConfigureMethodCall($serviceDefinition)) {
                continue;
            }

            $emptyConfigurableRectorClasses[] = $serviceId;
        }

        return $emptyConfigurableRectorClasses;
    }

    private function hasConfigureMethodCall(Definition $definition): bool
    {
        foreach ($definition->getMethodCalls() as $methodCall) {
            if ($methodCall[0] === 'configure') {
                if (! isset($methodCall[1][0])) {
                    return false;
                }

                return $methodCall[1][0] !== [];
            }
        }

        return false;
    }
}
