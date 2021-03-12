<?php

namespace Rector\Tests\Sensio\Rector\ClassMethod\RemoveServiceFromSensioRouteRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

final class SomeClass
{
    /**
     * @Route(service="some_service")
     */
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Sensio\Rector\ClassMethod\RemoveServiceFromSensioRouteRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

final class SomeClass
{
    /**
     * @Route()
     */
    public function run()
    {
    }
}

?>
