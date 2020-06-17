<?php

declare(strict_types=1);

namespace Rector\Core\HttpKernel;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\DependencyInjection\Collector\RectorServiceArgumentCollector;
use Rector\Core\DependencyInjection\CompilerPass\MakeRectorsPublicCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\MergeImportedRectorServiceArgumentsCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\RemoveExcludedRectorsCompilerPass;
use Rector\Core\DependencyInjection\Loader\TolerantRectorYamlFileLoader;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\AutoBindParameter\DependencyInjection\CompilerPass\AutoBindParameterCompilerPass;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\ConsoleColorDiff\ConsoleColorDiffBundle;
use Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireInterfacesCompilerPass;
use Symplify\ParameterNameGuard\Bundle\ParameterNameGuardBundle;

final class RectorKernel extends Kernel implements ExtraConfigAwareKernelInterface
{
    /**
     * @var string[]
     */
    private $configs = [];

    /**
     * @var RectorServiceArgumentCollector
     */
    private $rectorServiceArgumentCollector;

    public function __construct(string $environment, bool $debug)
    {
        $this->rectorServiceArgumentCollector = new RectorServiceArgumentCollector();

        parent::__construct($environment, $debug);
    }

    public function getCacheDir(): string
    {
        // manually configured, so it can be replaced in phar
        return sys_get_temp_dir() . '/_rector';
    }

    public function getLogDir(): string
    {
        // manually configured, so it can be replaced in phar
        return sys_get_temp_dir() . '/_rector_log';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/config.yaml');

        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }

    /**
     * @param string[] $configs
     */
    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [new ConsoleColorDiffBundle(), new ParameterNameGuardBundle()];
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new RemoveExcludedRectorsCompilerPass());

        $containerBuilder->addCompilerPass(new AutowireArrayParameterCompilerPass());

        // autowire Rectors by default (mainly for 3rd party code)
        $containerBuilder->addCompilerPass(new AutowireInterfacesCompilerPass([RectorInterface::class]));

        $containerBuilder->addCompilerPass(new AutoBindParameterCompilerPass());
        $containerBuilder->addCompilerPass(new MakeRectorsPublicCompilerPass());

        // add all merged arguments of Rector services
        $containerBuilder->addCompilerPass(
            new MergeImportedRectorServiceArgumentsCompilerPass($this->rectorServiceArgumentCollector)
        );
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
            new TolerantRectorYamlFileLoader($container, $kernelFileLocator, $this->rectorServiceArgumentCollector),
        ]);

        return new DelegatingLoader($loaderResolver);
    }
}
