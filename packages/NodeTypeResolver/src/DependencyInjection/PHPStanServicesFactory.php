<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\DependencyInjection;

use Nette\DI\Container;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use PHPStan\DependencyInjection\ContainerFactory;

final class PHPStanServicesFactory
{
    /**
     * @var Container
     */
    private $container;

    public function __construct()
    {
        $this->container = (new ContainerFactory(getcwd()))
            ->create(sys_get_temp_dir(), [], []);
    }

    public function createBroker(): Broker
    {
        return $this->container->getByType(Broker::class);
    }

    public function createNodeScopeResolver(): NodeScopeResolver
    {
        return $this->container->getByType(NodeScopeResolver::class);
    }

    public function createTypeSpecifier(): TypeSpecifier
    {
        return $this->container->getByType(TypeSpecifier::class);
    }

    public function createScopeFactory(): ScopeFactory
    {
        return $this->container->getByType(ScopeFactory::class);
    }
}
