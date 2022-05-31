<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\Symfony\Routing;

use Rector\Symfony\Bridge\Symfony\ContainerServiceProvider;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;
use Rector\Symfony\ValueObject\SymfonyRouteMetadata;
use RectorPrefix20220531\Symfony\Component\Routing\RouterInterface;
use RectorPrefix20220531\Webmozart\Assert\Assert;
final class SymfonyRoutesProvider implements \Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface
{
    /**
     * @var SymfonyRouteMetadata[]
     */
    private $symfonyRouteMetadatas = [];
    /**
     * @readonly
     * @var \Rector\Symfony\Bridge\Symfony\ContainerServiceProvider
     */
    private $containerServiceProvider;
    public function __construct(\Rector\Symfony\Bridge\Symfony\ContainerServiceProvider $containerServiceProvider)
    {
        $this->containerServiceProvider = $containerServiceProvider;
    }
    /**
     * @return SymfonyRouteMetadata[]
     */
    public function provide() : array
    {
        if ($this->symfonyRouteMetadatas !== []) {
            return $this->symfonyRouteMetadatas;
        }
        $router = $this->containerServiceProvider->provideByName('router');
        \RectorPrefix20220531\Webmozart\Assert\Assert::isInstanceOf($router, 'Symfony\\Component\\Routing\\RouterInterface');
        $symfonyRoutesMetadatas = [];
        /** @var RouterInterface $router */
        $routeCollection = $router->getRouteCollection();
        // route name is hidden in the key - https://github.com/symfony/symfony/blob/4dde1619d6c65b662170a6a3cbbdc7092eeb1fa2/src/Symfony/Component/Routing/RouteCollection.php#L99
        foreach ($routeCollection->all() as $routeName => $route) {
            $symfonyRoutesMetadatas[] = new \Rector\Symfony\ValueObject\SymfonyRouteMetadata($routeName, $route->getPath(), $route->getDefaults(), $route->getRequirements(), $route->getHost(), $route->getSchemes(), $route->getMethods(), $route->getCondition());
        }
        $this->symfonyRouteMetadatas = $symfonyRoutesMetadatas;
        return $symfonyRoutesMetadatas;
    }
}
