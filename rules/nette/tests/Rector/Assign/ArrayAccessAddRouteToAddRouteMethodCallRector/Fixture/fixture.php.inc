<?php

namespace Rector\Nette\Tests\Rector\Assign\ArrayAccessAddRouteToAddRouteMethodCallRector\Fixture;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    public static function createRouter()
    {
        $routeList = new RouteList();
        $routeList[] = new Route('<module>/<presenter>/<action>[/<id>]', 'Homepage:default');
        return $routeList;
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\Assign\ArrayAccessAddRouteToAddRouteMethodCallRector\Fixture;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    public static function createRouter()
    {
        $routeList = new RouteList();
        $routeList->addRoute('<module>/<presenter>/<action>[/<id>]', 'Homepage:default');
        return $routeList;
    }
}

?>
