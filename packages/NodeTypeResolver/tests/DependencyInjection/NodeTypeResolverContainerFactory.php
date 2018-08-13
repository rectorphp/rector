<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\DependencyInjection;

use Psr\Container\ContainerInterface;

final class NodeTypeResolverContainerFactory
{
    public function create(): ContainerInterface
    {
        $nodeTypeResolverKernel = new NodeTypeResolverKernel('dev', true);

        $nodeTypeResolverKernel->boot();

        return $nodeTypeResolverKernel->getContainer();
    }
}
