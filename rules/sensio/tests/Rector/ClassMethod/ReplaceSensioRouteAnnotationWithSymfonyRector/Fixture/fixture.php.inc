<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

final class Fixture
{
    /**
     * @Route()
     */
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class Fixture
{
    /**
     * @Route()
     */
    public function run()
    {
    }
}

?>
