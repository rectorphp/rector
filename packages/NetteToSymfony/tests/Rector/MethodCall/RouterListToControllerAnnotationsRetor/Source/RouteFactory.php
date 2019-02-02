<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Source;

final class RouteFactory
{
    public static function get(string $path, string $presenterClass): Route
    {
        return new Route($path, $presenterClass);
    }
}
