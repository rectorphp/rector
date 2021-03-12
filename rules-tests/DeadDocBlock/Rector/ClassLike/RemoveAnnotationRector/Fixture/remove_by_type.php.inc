<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassLike\RemoveAnnotationRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

final class RemoveByType
{
    /**
     * @DI\InjectParams({
     *     "subscribeService" = @DI\Inject("app.email.service.subscribe"),
     *     "ipService" = @DI\Inject("app.util.service.ip")
     * })
     */
    public function __construct()
    {
    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassLike\RemoveAnnotationRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

final class RemoveByType
{
    public function __construct()
    {
    }
}

?>
