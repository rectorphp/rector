<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRector\Fixture;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class ConstantReferenceRouterFactory
{
    const SOME_PATH = '/some-path';

    public function create(): RouteList
    {
        $routeList = new RouteList();

        // case of single action controller, usually get() or __invoke() method
        $routeList[] = new Route(self::SOME_PATH, ConstantReferenceSomePresenter::class);

        return $routeList;
    }
}

final class ConstantReferenceSomePresenter
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

final class ConstantReferenceRouterFactory
{
    const SOME_PATH = '/some-path';

    public function create(): RouteList
    {
        $routeList = new RouteList();

        return $routeList;
    }
}

final class ConstantReferenceSomePresenter
{
    /**
     * @\Symfony\Component\Routing\Annotation\Route(path="/some-path")
     */
    public function run()
    {
    }
}

?>
