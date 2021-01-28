<?php

namespace Rector\Transform\Tests\Rector\Assign\DimFetchAssignToMethodCallRector\Fixture;

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

namespace Rector\Transform\Tests\Rector\Assign\DimFetchAssignToMethodCallRector\Fixture;

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
