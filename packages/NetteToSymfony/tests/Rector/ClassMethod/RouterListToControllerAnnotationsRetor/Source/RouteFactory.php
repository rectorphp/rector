<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source;

use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\Route;

final class RouteFactory
{
    public static function get(string $path, string $presenterClass): Route
    {
        return new Route($path, $presenterClass);
    }
}
