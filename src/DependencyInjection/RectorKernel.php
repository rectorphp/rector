<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Rector\DependencyInjection\CompilerPass\CollectorCompilerPass;
use Rector\YamlRector\DependencyInjection\YamlRectorCollectorCompilerPass;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\BetterPhpDocParser\DependencyInjection\CompilerPass\CollectDecoratorsToPhpDocInfoFactoryCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutoBindParametersCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireDefaultCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireSinglyImplementedCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\PublicDefaultCompilerPass;
use Symplify\PackageBuilder\Yaml\FileLoader\ParameterImportsYamlFileLoader;

final class RectorKernel extends Kernel
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

        // debug: true is require to invalidate container on service files change
        parent::__construct('cli' . sha1((string) $configFile), true);
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
        $containerBuilder->addCompilerPass(new YamlRectorCollectorCompilerPass());
        $containerBuilder->addCompilerPass(new AutowireDefaultCompilerPass());

        // for symplify/better-php-doc-parser
        $containerBuilder->addCompilerPass(new CollectDecoratorsToPhpDocInfoFactoryCompilerPass());

        // for defaults
        $containerBuilder->addCompilerPass(new PublicDefaultCompilerPass());
        $containerBuilder->addCompilerPass(new AutowireSinglyImplementedCompilerPass());

        $containerBuilder->addCompilerPass(new AutoBindParametersCompilerPass());
    }

    /**
     * This allows to use "%vendor%" variables in imports
     * @param ContainerInterface|ContainerBuilder $container
     */
    protected function getContainerLoader(ContainerInterface $container): DelegatingLoader
    {
        $kernelFileLocator = new FileLocator($this);

        $loaderResolver = new LoaderResolver([
            new GlobFileLoader($kernelFileLocator),
            new ParameterImportsYamlFileLoader($container, $kernelFileLocator),
        ]);

        return new DelegatingLoader($loaderResolver);
    }
}
