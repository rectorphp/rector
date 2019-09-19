<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Fixture;

use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\RouteFactory;
use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\RouteList;

final class StaticRouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();

        // case of single action controller, usually get() or __invoke() method
        $routeList[] = RouteFactory::get('some-path', StaticSomePresenter::class);

        return $routeList;
    }
}

final class StaticSomePresenter
{
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Fixture;

use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\RouteFactory;
use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\RouteList;
use Symfony\Component\Routing\Annotation\Route;

final class StaticRouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();

        return $routeList;
    }
}

final class StaticSomePresenter
{
    /**
     * @Route(path="some-path", methods={"GET"})
     */
    public function run()
    {
    }
}

?>
