<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Rector\DependencyInjection\CompilerPass\CollectorCompilerPass;
use Rector\NodeTypeResolver\DependencyInjection\CompilerPass\NodeTypeResolverCollectorCompilerPass;
use Rector\NodeValueResolver\DependencyInjection\CompilerPass\NodeValueResolverCollectorCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernel extends Kernel
{
    /**
     * @var string
     */
    private $configFile;

    public function __construct(?string $configFile = '')
    {
        if ($configFile) {
            $this->configFile = $configFile;
        }

        parent::__construct('dev' . sha1($configFile), true);
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
        return [
            new RectorBundle(),
        ];
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new CollectorCompilerPass());
        $containerBuilder->addCompilerPass(new NodeValueResolverCollectorCompilerPass());
        $containerBuilder->addCompilerPass(new NodeTypeResolverCollectorCompilerPass());
    }
}
