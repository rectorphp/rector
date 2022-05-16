<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject\RouteMap;

use Rector\Symfony\ValueObject\SymfonyRouteMetadata;
final class RouteMap
{
    /**
     * @var SymfonyRouteMetadata[]
     * @readonly
     */
    private $routes;
    /**
     * @param SymfonyRouteMetadata[] $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }
    public function hasRoutes() : bool
    {
        return $this->routes !== [];
    }
    public function getRouteByMethod(string $methodName) : ?\Rector\Symfony\ValueObject\SymfonyRouteMetadata
    {
        foreach ($this->routes as $route) {
            if ($route->getDefault('_controller') === $methodName) {
                return $route;
            }
        }
        return null;
    }
}
