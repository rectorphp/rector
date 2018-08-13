<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\DependencyInjection;

use Psr\Container\ContainerInterface;

final class NodeTypeResolverContainerFactory
{
    public function create(): ContainerInterface
    {
        $kernel = new NodeTypeResolverKernel();
        $kernel->boot();

        return $kernel->getContainer();
    }

    public function createWithConfig(string $config): ContainerInterface
    {
        $kernel = new NodeTypeResolverKernel($config);
        $kernel->boot();

        return $kernel->getContainer();
    }
}
