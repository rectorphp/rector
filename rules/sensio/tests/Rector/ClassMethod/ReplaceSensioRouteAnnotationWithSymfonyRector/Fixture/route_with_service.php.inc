<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/some_route", service="some_service")
 */
final class RouteWithService
{
}

?>
-----
<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/some_route")
 */
final class RouteWithService
{
}

?>
