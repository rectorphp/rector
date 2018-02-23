<?php declare(strict_types=1);

namespace Rector\RectorBuilder\DependencyInjection\CompilerPass;

use Rector\Rector\RectorCollector;
use Rector\RectorBuilder\Contract\RectorProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\DependencyInjection\DefinitionFinder;

final class RectorProvidersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $rectorCollectorDefinition = DefinitionFinder::getByType($containerBuilder, RectorCollector::class);
        $rectorProviderDefinitions = DefinitionFinder::findAllByType($containerBuilder, RectorProviderInterface::class);

        foreach ($rectorProviderDefinitions as $rectorProviderDefinition) {
            $methodCallArgument = sprintf("@=service('%s').provide()", $rectorProviderDefinition->getClass());
            $rectorCollectorDefinition->addMethodCall('addRector', [$methodCallArgument]);
        }
    }
}
