<?php

declare (strict_types=1);
namespace Rector\Core\Validation\Collector;

use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use RectorPrefix20220209\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220209\Symfony\Component\DependencyInjection\Definition;
/**
 * @see \Rector\Core\Tests\Validation\Collector\EmptyConfigurableRectorCollector\EmptyConfigurableRectorCollectorTest
 */
final class EmptyConfigurableRectorCollector
{
    /**
     * @readonly
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $containerBuilder;
    public function __construct(\RectorPrefix20220209\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;
    }
    /**
     * @return array<class-string<ConfigurableRectorInterface>>
     */
    public function resolveEmptyConfigurableRectorClasses() : array
    {
        $emptyConfigurableRectorClasses = [];
        foreach ($this->containerBuilder->getServiceIds() as $serviceId) {
            if (!\is_a($serviceId, \Rector\Core\Contract\Rector\ConfigurableRectorInterface::class, \true)) {
                continue;
            }
            if (\is_a($serviceId, \Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface::class, \true)) {
                continue;
            }
            // it seems always loaded
            if (\is_a($serviceId, \Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector::class, \true)) {
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
    private function hasConfigureMethodCall(\RectorPrefix20220209\Symfony\Component\DependencyInjection\Definition $definition) : bool
    {
        foreach ($definition->getMethodCalls() as $methodCall) {
            if ($methodCall[0] === 'configure') {
                if (!isset($methodCall[1][0])) {
                    return \false;
                }
                return $methodCall[1][0] !== [];
            }
        }
        return \false;
    }
}
