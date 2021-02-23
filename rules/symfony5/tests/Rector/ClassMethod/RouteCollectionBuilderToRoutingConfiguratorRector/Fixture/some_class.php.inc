<?php

namespace Rector\Symfony5\Tests\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
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

    public function registerBundles()
    {
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}

?>
-----
<?php

namespace Rector\Symfony5\Tests\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
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

    public function registerBundles()
    {
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}

?>
