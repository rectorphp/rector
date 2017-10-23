<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\DependencyInjection\CompilerPass;

use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\Adapter\Symfony\DependencyInjection\DefinitionCollector;

final class NodeTypeResolverCollectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $this->collectPerNodeTypeResolversToValueResolver($containerBuilder);
        $this->setNodeTypeResolverToAware($containerBuilder);
    }

    private function collectPerNodeTypeResolversToValueResolver(ContainerBuilder $containerBuilder): void
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
