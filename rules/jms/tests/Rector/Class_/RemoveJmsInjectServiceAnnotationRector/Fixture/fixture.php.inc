<?php

namespace Rector\JMS\Tests\Rector\Class_\RemoveJmsInjectServiceAnnotationRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("email.web.services.subscribe_token", public=true)
 */
class Fixture
{
}

?>
-----
<?php

namespace Rector\JMS\Tests\Rector\Class_\RemoveJmsInjectServiceAnnotationRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class Fixture
{
}

?>
