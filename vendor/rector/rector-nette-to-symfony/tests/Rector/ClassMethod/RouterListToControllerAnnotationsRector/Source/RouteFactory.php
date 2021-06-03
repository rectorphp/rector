<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRector\Source;

use RectorPrefix20210603\Nette\Application\Routers\Route;
final class RouteFactory
{
    public static function get(string $path, string $presenterClass) : \RectorPrefix20210603\Nette\Application\Routers\Route
    {
        return new \RectorPrefix20210603\Nette\Application\Routers\Route($path, $presenterClass);
    }
}
