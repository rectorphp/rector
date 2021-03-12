<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRector\Fixture;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class NewRouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();

        // case of single action controller, usually get() or __invoke() method
        $routeList[] = new Route('some-path', NewSomePresenter::class);

        return $routeList;
    }
}

final class NewSomePresenter
{
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRector\Fixture;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class NewRouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();

        return $routeList;
    }
}

final class NewSomePresenter
{
    /**
     * @\Symfony\Component\Routing\Annotation\Route(path="some-path")
     */
    public function run()
    {
    }
}

?>
