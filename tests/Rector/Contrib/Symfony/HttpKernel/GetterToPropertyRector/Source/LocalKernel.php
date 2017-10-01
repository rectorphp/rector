<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\HttpKernel\GetterToPropertyRector\Source;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class LocalKernel extends Kernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/services.yml');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/_rector_tests_local_kernel_cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/_rector_tests_local_kernel_logs';
    }
}
