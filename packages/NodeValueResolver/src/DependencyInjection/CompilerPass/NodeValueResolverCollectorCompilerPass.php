<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\DependencyInjection\CompilerPass;

use Rector\NodeValueResolver\Contract\NodeValueResolverAwareInterface;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\NodeValueResolver\NodeValueResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\DependencyInjection\DefinitionCollector;

final class NodeValueResolverCollectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $this->collectPerNodeValueResolversToValueResolver($containerBuilder);
        $this->setNodeValueResolverToAware($containerBuilder);
    }

    private function collectPerNodeValueResolversToValueResolver(ContainerBuilder $containerBuilder): void
    {
        DefinitionCollector::loadCollectorWithType(
            $containerBuilder,
            NodeValueResolver::class,
            PerNodeValueResolverInterface::class,
            'addPerNodeValueResolver'
        );
    }

    private function setNodeValueResolverToAware(ContainerBuilder $containerBuilder): void
    {
        DefinitionCollector::loadCollectorWithType(
            $containerBuilder,
            NodeValueResolverAwareInterface::class,
            NodeValueResolver::class,
            'setNodeValueResolver'
        );
    }
}
