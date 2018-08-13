<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\DependencyInjection;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class NodeTypeResolverKernel extends Kernel
{
    /**
     * @var null|string
     */
    private $config;

    public function __construct(?string $config = null)
    {
        $this->config = $config;

        parent::__construct('dev', true);
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../src/config/services.yml');

        if ($this->config) {
            $loader->load($this->config);
        }
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
        return sys_get_temp_dir() . '/_rector_node_type_resolver_cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/_rector_type_resolver_test_log';
    }
}
