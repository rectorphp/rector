<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\DependencyInjection\CompilerPass;

use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeCallerTypeResolver\PerNodeCallerTypeResolverInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeCallerTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\DependencyInjection\DefinitionCollector;

final class NodeTypeResolverCollectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $this->collectPerNodeTypeResolversToNodeTypeResolver($containerBuilder);
        $this->collectPerCallerNodeTypeResolversToCallerNodeTypeResolver($containerBuilder);
        $this->setNodeTypeResolverToAware($containerBuilder);
    }

    private function collectPerCallerNodeTypeResolversToCallerNodeTypeResolver(ContainerBuilder $containerBuilder): void
    {
        DefinitionCollector::loadCollectorWithType(
            $containerBuilder,
            NodeCallerTypeResolver::class,
            PerNodeCallerTypeResolverInterface::class,
            'addPerNodeCallerTypeResolver'
        );
    }

    private function collectPerNodeTypeResolversToNodeTypeResolver(ContainerBuilder $containerBuilder): void
    {
        DefinitionCollector::loadCollectorWithType(
            $containerBuilder,
            NodeTypeResolver::class,
            PerNodeTypeResolverInterface::class,
            'addPerNodeTypeResolver'
        );
    }

    private function setNodeTypeResolverToAware(ContainerBuilder $containerBuilder): void
    {
        DefinitionCollector::loadCollectorWithType(
            $containerBuilder,
            NodeTypeResolverAwareInterface::class,
            NodeTypeResolver::class,
            'setNodeTypeResolver'
        );
    }
}
