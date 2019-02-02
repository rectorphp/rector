<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Source\Route;
use Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Source\RouteList;

final class ConstantReferenceRouterFactory
{
    const SOME_PATH = '/some-path';

    public function create(): RouteList
    {
        $routeList = new RouteList();

        // case of single action controller, usually get() or __invoke() method
        $routeList[] = Route::get(self::SOME_PATH, ConstantReferenceSomePresenter::class);

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

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Source\Route;
use Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Source\RouteList;

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
     * @\Symfony\Component\Routing\Annotation\Route(path="/some-path", methods={"GET"})
     */
    public function run()
    {
    }
}

?>
