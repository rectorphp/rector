<?php

declare(strict_types=1);

namespace Rector\HttpKernel;

use Phar;
use Rector\Contract\Rector\RectorInterface;
use Rector\DependencyInjection\Collector\RectorServiceArgumentCollector;
use Rector\DependencyInjection\CompilerPass\MergeImportedRectorServiceArgumentsCompilerPass;
use Rector\DependencyInjection\CompilerPass\RemoveExcludedRectorsCompilerPass;
use Rector\DependencyInjection\Loader\TolerantRectorYamlFileLoader;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\AutoBindParameter\DependencyInjection\CompilerPass\AutoBindParameterCompilerPass;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutoReturnFactoryCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireInterfacesCompilerPass;

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
        return [];
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new RemoveExcludedRectorsCompilerPass());

        $containerBuilder->addCompilerPass(new AutoReturnFactoryCompilerPass());
        $containerBuilder->addCompilerPass(new AutowireArrayParameterCompilerPass());

        // autowire Rectors by default (mainly for 3rd party code)
        $containerBuilder->addCompilerPass(new AutowireInterfacesCompilerPass([RectorInterface::class]));

        $containerBuilder->addCompilerPass(new AutoBindParameterCompilerPass());

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

    protected function initializeContainer()
    {
        $class = $this->getContainerClass();
        $cacheDir = $this->getCacheDir();

        $collectDeprecations = ($this->debug && !\defined('PHPUNIT_COMPOSER_INSTALL') && '' === Phar::running(false));

        if ($collectDeprecations) {
            $collectedLogs = [];
            $previousHandler = set_error_handler(static function ($type, $message, $file, $line) use (&$collectedLogs, &$previousHandler) {
                if (E_USER_DEPRECATED !== $type && E_DEPRECATED !== $type) {
                    return $previousHandler ? $previousHandler($type, $message, $file, $line) : false;
                }

                if (isset($collectedLogs[$message])) {
                    ++$collectedLogs[$message]['count'];

                    return null;
                }

                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
                // Clean the trace by removing first frames added by the error handler itself.
                for ($i = 0; isset($backtrace[$i]); ++$i) {
                    if (isset($backtrace[$i]['file'], $backtrace[$i]['line']) && $backtrace[$i]['line'] === $line && $backtrace[$i]['file'] === $file) {
                        $backtrace = \array_slice($backtrace, 1 + $i);
                        break;
                    }
                }
                // Remove frames added by DebugClassLoader.
                for ($i = \count($backtrace) - 2; 0 < $i; --$i) {
                    if (\in_array($backtrace[$i]['class'] ?? null, [DebugClassLoader::class, LegacyDebugClassLoader::class], true)) {
                        $backtrace = [$backtrace[$i + 1]];
                        break;
                    }
                }

                $collectedLogs[$message] = [
                    'type' => $type,
                    'message' => $message,
                    'file' => $file,
                    'line' => $line,
                    'trace' => [$backtrace[0]],
                    'count' => 1,
                ];

                return null;
            });
        }

        try {
            $container = null;
            $container = $this->buildContainer();
            $container->compile();
        } finally {
            if ($collectDeprecations) {
                restore_error_handler();

                file_put_contents($cacheDir.'/'.$class.'Deprecations.log', serialize(array_values($collectedLogs)));
                file_put_contents($cacheDir.'/'.$class.'Compiler.log', null !== $container ? implode("\n", $container->getCompiler()->getLog()) : '');
            }
        }

        $this->container = $container;
        $this->container->set('kernel', $this);

        if ($this->container->has('cache_warmer')) {
            $this->container->get('cache_warmer')->warmUp($this->container->getParameter('kernel.cache_dir'));
        }
    }
}
