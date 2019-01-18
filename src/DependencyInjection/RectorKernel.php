<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Rector\Contract\Rector\PhpRectorInterface;
use Rector\DependencyInjection\CompilerPass\RemoveExcludedRectorsCompilerPass;
use Rector\DependencyInjection\Loader\TolerantRectorYamlFileLoader;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutoBindParametersCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireInterfacesCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireSinglyImplementedCompilerPass;
use Symplify\PackageBuilder\HttpKernel\SimpleKernelTrait;

final class RectorKernel extends Kernel
{
    use SimpleKernelTrait;

    /**
     * @var string[]
     */
    private $extraConfigFiles = [];

    /**
     * @param string[] $configFiles
     */
    public function __construct(array $configFiles = [])
    {
        $this->extraConfigFiles = $configFiles;

        $configFilesHash = md5(serialize($configFiles));

        // debug: require to invalidate container on service files change
        parent::__construct('cli_' . $configFilesHash, true);
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../config/config.yml');

        foreach ($this->extraConfigFiles as $extraConfigFile) {
            $loader->load($extraConfigFile);
        }
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new RemoveExcludedRectorsCompilerPass());

        // for defaults
        $containerBuilder->addCompilerPass(new AutowireSinglyImplementedCompilerPass());
        $containerBuilder->addCompilerPass(new AutowireArrayParameterCompilerPass());

        // autowire Rectors by default (mainly for 3rd party code)
        $containerBuilder->addCompilerPass(new AutowireInterfacesCompilerPass([
            PhpRectorInterface::class,
            FileSystemRectorInterface::class,
        ]));

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
            new TolerantRectorYamlFileLoader($container, $kernelFileLocator),
        ]);

        return new DelegatingLoader($loaderResolver);
    }
}
