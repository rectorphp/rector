<?php

namespace Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Source\SomeRoute;
use Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Source\SomeRouteList;

final class RouterFactory
{
    public static function createRouter()
    {
        $routeList = new SomeRouteList();
        $routeList[] = new SomeRoute('<module>/<presenter>/<action>[/<id>]', 'Homepage:default');
        return $routeList;
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Source\SomeRoute;
use Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\Source\SomeRouteList;

final class RouterFactory
{
    public static function createRouter()
    {
        $routeList = new SomeRouteList();
        $routeList->addRoute('<module>/<presenter>/<action>[/<id>]', 'Homepage:default');
        return $routeList;
    }
}

?>
