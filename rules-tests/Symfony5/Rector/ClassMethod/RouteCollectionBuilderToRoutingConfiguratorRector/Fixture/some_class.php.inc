<?php

namespace Rector\Tests\Symfony5\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class ConcreteMicroKernel extends Kernel
{
    use MicroKernelTrait;

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/admin', 'App\Controller\AdminController::dashboard', 'admin_dashboard');
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

namespace Rector\Tests\Symfony5\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class ConcreteMicroKernel extends Kernel
{
    use MicroKernelTrait;

    protected function configureRouting(\Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator $routes): void
    {
        $routes->add('admin_dashboard', '/admin')->controller('App\Controller\AdminController::dashboard');
    }

    public function registerBundles()
    {
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}

?>
