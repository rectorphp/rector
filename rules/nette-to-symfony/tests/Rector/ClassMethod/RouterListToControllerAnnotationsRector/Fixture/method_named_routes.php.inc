<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRector\Fixture;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class MethodNamedRoutesRouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();
        $routeList[] = new Route('hi', 'MethodNamedRoutesSome:first');
        $routeList[] = new Route('hello', 'MethodNamedRoutesSome:second');
        $routeList[] = new Route('<presenter>/<action>', 'Homepage:default');

        return $routeList;
    }
}

final class MethodNamedRoutesSomePresenter
{
    public function actionFirst()
    {
    }

    public function actionSecond()
    {
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;
use Nette\Application\Routers\RouteList;

final class MethodNamedRoutesRouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();

        return $routeList;
    }
}

final class MethodNamedRoutesSomePresenter
{
    /**
     * @Route(path="hi")
     */
    public function actionFirst()
    {
    }

    /**
     * @Route(path="hello")
     */
    public function actionSecond()
    {
    }
}

?>
