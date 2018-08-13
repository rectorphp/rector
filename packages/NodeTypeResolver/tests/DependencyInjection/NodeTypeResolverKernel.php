<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\DependencyInjection;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class NodeTypeResolverKernel extends Kernel
{
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../src/config/services.yml');
        $loader->load(__DIR__ . '/../config/config.tests.yml');
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [];
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/_node_type_resolver_test_cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/_node_type_resolver_test_log';
    }
}
