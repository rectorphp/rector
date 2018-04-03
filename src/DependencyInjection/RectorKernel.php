<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Rector\DependencyInjection\CompilerPass\AutowireRectorCompilerPass;
use Rector\DependencyInjection\CompilerPass\CollectorCompilerPass;
use Rector\NodeTypeResolver\DependencyInjection\CompilerPass\NodeTypeResolverCollectorCompilerPass;
use Rector\RectorBuilder\DependencyInjection\CompilerPass\RectorProvidersCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireSinglyImplementedCompilerPass;

final class RectorKernel extends Kernel
{
    /**
     * @var string
     */
    private $configFile;

    public function __construct(?string $configFile = '')
    {
        if ($configFile !== null) {
            $this->configFile = $configFile;
        }

        // debug: true is require to invalidate container on service files change
        parent::__construct('cli' . sha1($configFile), true);
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../config/config.yml');

        if ($this->configFile) {
            $loader->load($this->configFile);
        }
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/_rector_cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/_rector_log';
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [];
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new CollectorCompilerPass());
        $containerBuilder->addCompilerPass(new AutowireRectorCompilerPass());
        $containerBuilder->addCompilerPass(new RectorProvidersCompilerPass());
        $containerBuilder->addCompilerPass(new NodeTypeResolverCollectorCompilerPass());
        $containerBuilder->addCompilerPass(new AutowireSinglyImplementedCompilerPass());
    }
}
