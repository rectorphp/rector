<?php declare(strict_types=1);

namespace Rector\PharBuilder\DependencyInjection;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class PharBuilderKernel extends Kernel
{
    public function __construct()
    {
        // debug: true is require to invalidate container on service files change
        parent::__construct('cli', true);
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../config/config.yml');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/_rector_phar_builder_cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/_rector_phar_bulider_log';
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [];
    }
}
