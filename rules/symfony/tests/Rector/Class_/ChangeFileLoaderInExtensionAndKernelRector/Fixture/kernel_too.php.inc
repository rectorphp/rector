<?php

namespace Rector\Symfony\Tests\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector\Fixture;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class KernelToo extends Kernel
{
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/config.xml');
    }

    public function registerBundles()
    {
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector\Fixture;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class KernelToo extends Kernel
{
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/config.yaml');
    }

    public function registerBundles()
    {
    }
}

?>
