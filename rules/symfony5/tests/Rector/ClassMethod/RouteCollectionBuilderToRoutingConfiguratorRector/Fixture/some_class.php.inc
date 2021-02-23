<?php

namespace Rector\Symfony5\Tests\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class ConcreteMicroKernel extends Kernel
{
    use MicroKernelTrait;

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/', 'kernel::halloweenAction');
        $routes->add('/danger', 'kernel::dangerousAction');
    }
}

?>
-----
<?php

namespace Rector\Symfony5\Tests\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class ConcreteMicroKernel extends Kernel
{
    use MicroKernelTrait;

    protected function configureRouting(RoutingConfigurator $routes): void
    {
        $routes->add('halloween', '/')->controller('kernel::halloweenAction');
        $routes->add('danger', '/danger')->controller('kernel::dangerousAction');
    }
}

?>
