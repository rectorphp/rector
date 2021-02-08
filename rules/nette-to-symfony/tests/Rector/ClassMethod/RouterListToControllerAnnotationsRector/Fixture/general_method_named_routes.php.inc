<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRector\Fixture;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class GeneralMethodNamedRoutesRouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();
        $routeList[] = new Route('<presenter>/<action>', 'Homepage:default');

        return $routeList;
    }
}

final class GeneralMethodNamedRoutesSomePresenter
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

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class GeneralMethodNamedRoutesRouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();

        return $routeList;
    }
}

final class GeneralMethodNamedRoutesSomePresenter
{
    /**
     * @\Symfony\Component\Routing\Annotation\Route(path="general-method-named-routes-some/first")
     */
    public function actionFirst()
    {
    }

    /**
     * @\Symfony\Component\Routing\Annotation\Route(path="general-method-named-routes-some/second")
     */
    public function actionSecond()
    {
    }
}

?>
