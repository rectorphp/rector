<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Collector;

use Rector\ZendToSymfony\ValueObject\RouteValueObject;

final class RouteCollector
{
    /**
     * @var RouteValueObject[]
     */
    private $routeValueObjects = [];

    public function addRouteValueObject(RouteValueObject $routeValueObject): void
    {
        $this->routeValueObjects[] = $routeValueObject;
    }

    /**
     * @return RouteValueObject[]
     */
    public function getRouteValueObjects(): array
    {
        return $this->routeValueObjects;
    }
}
