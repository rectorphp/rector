<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Fixture;

use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\Route;
use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\RouteList;

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

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Fixture;

use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\RouteList;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route(path="general-method-named-routes-some/first")
     */
    public function actionFirst()
    {
    }

    /**
     * @Route(path="general-method-named-routes-some/second")
     */
    public function actionSecond()
    {
    }
}

?>
