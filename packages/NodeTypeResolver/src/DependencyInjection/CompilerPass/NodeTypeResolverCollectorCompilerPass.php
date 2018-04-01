<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\DependencyInjection\CompilerPass;

use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\DependencyInjection\DefinitionCollector;
use Symplify\PackageBuilder\DependencyInjection\DefinitionFinder;

final class NodeTypeResolverCollectorCompilerPass implements CompilerPassInterface
{
    /**
     * @var DefinitionCollector
     */
    private $definitionCollector;

    public function __construct()
    {
        $this->definitionCollector = (new DefinitionCollector(new DefinitionFinder()));
    }

    public function process(ContainerBuilder $containerBuilder): void
    {
        $this->collectPerNodeTypeResolversToNodeTypeResolver($containerBuilder);
        $this->setNodeTypeResolverToAware($containerBuilder);
    }

    private function collectPerNodeTypeResolversToNodeTypeResolver(ContainerBuilder $containerBuilder): void
    {
        $this->definitionCollector->loadCollectorWithType(
            $containerBuilder,
            NodeTypeResolver::class,
            PerNodeTypeResolverInterface::class,
            'addPerNodeTypeResolver'
        );
    }

    private function setNodeTypeResolverToAware(ContainerBuilder $containerBuilder): void
    {
        $this->definitionCollector->loadCollectorWithType(
            $containerBuilder,
            NodeTypeResolverAwareInterface::class,
            NodeTypeResolver::class,
            'setNodeTypeResolver'
        );
    }
}
