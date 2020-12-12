<?php

namespace Rector\JMS\Tests\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class Fixture
{
    /**
     * @DI\InjectParams({
     *     "subscribeService" = @DI\Inject("app.email.service.subscribe"),
     *     "ipService" = @DI\Inject("app.util.service.ip")
     * })
     */
    public function __construct() {
    }
}

?>
-----
<?php

namespace Rector\JMS\Tests\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class Fixture
{
    public function __construct() {
    }
}

?>
