<?php

declare(strict_types=1);

namespace Rector\Core\HttpKernel;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use Rector\Core\DependencyInjection\CompilerPass\DeprecationWarningCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\MakeRectorsPublicCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\MergeImportedRectorConfigureCallValuesCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\RemoveSkippedRectorsCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\VerifyRectorServiceExistsCompilerPass;
use Rector\Core\DependencyInjection\Loader\ConfigurableCallValuesCollectingPhpFileLoader;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\ComposerJsonManipulator\Bundle\ComposerJsonManipulatorBundle;
use Symplify\ConsoleColorDiff\Bundle\ConsoleColorDiffBundle;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireInterfacesCompilerPass;
use Symplify\SimplePhpDocParser\Bundle\SimplePhpDocParserBundle;
use Symplify\Skipper\Bundle\SkipperBundle;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @todo possibly remove symfony/http-kernel and use the container build only
 */
final class RectorKernel extends Kernel
{
    private ConfigureCallValuesCollector $configureCallValuesCollector;

    /**
     * @param SmartFileInfo[] $configFileInfos
     */
    public function __construct(
        string $environment,
        bool $debug,
        private array $configFileInfos
    ) {
        $this->configureCallValuesCollector = new ConfigureCallValuesCollector();

        parent::__construct($environment, $debug);
    }

    public function getCacheDir(): string
    {
        $cacheDirectory = $_ENV['KERNEL_CACHE_DIRECTORY'] ?? null;
        if ($cacheDirectory !== null) {
            return $cacheDirectory . '/' . $this->environment;
        }

        // manually configured, so it can be replaced in phar
        return sys_get_temp_dir() . '/rector/cache';
    }

    public function getLogDir(): string
    {
        // manually configured, so it can be replaced in phar
        return sys_get_temp_dir() . '/rector/log';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/config.php');

        foreach ($this->configFileInfos as $configFileInfo) {
            $loader->load($configFileInfo->getRealPath());
        }
    }

    /**
     * @return iterable<BundleInterface>
     */
    public function registerBundles(): iterable
    {
        return [
            new ConsoleColorDiffBundle(),
            new ComposerJsonManipulatorBundle(),
            new SkipperBundle(),
            new SimplePhpDocParserBundle(),
        ];
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        // @see https://symfony.com/blog/new-in-symfony-4-4-dependency-injection-improvements-part-1
        $containerBuilder->setParameter('container.dumper.inline_factories', true);
        // to fix reincluding files again
        $containerBuilder->setParameter('container.dumper.inline_class_loader', false);

        // must run before AutowireArrayParameterCompilerPass, as the autowired array cannot contain removed services
        $containerBuilder->addCompilerPass(new RemoveSkippedRectorsCompilerPass());
        $containerBuilder->addCompilerPass(new AutowireArrayParameterCompilerPass());

        // autowire Rectors by default (mainly for tests)
        $containerBuilder->addCompilerPass(new AutowireInterfacesCompilerPass([RectorInterface::class]));
        $containerBuilder->addCompilerPass(new MakeRectorsPublicCompilerPass());

        $containerBuilder->addCompilerPass(new DeprecationWarningCompilerPass());

        // add all merged arguments of Rector services
        $containerBuilder->addCompilerPass(
            new MergeImportedRectorConfigureCallValuesCompilerPass($this->configureCallValuesCollector)
        );

        $containerBuilder->addCompilerPass(new VerifyRectorServiceExistsCompilerPass());
    }

    /**
     * This allows to use "%vendor%" variables in imports
     * @param ContainerInterface|ContainerBuilder $container
     */
    protected function getContainerLoader(ContainerInterface $container): DelegatingLoader
    {
        $fileLocator = new FileLocator($this);

        $loaderResolver = new LoaderResolver([
            new GlobFileLoader($fileLocator),
            new ConfigurableCallValuesCollectingPhpFileLoader(
                $container,
                $fileLocator,
                $this->configureCallValuesCollector
            ),
        ]);

        return new DelegatingLoader($loaderResolver);
    }
}
