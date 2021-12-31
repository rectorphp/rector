<?php

declare (strict_types=1);
namespace Rector\Core\Config\Loader;

use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use Rector\Core\DependencyInjection\Loader\ConfigurableCallValuesCollectingPhpFileLoader;
use RectorPrefix20211231\Symfony\Component\Config\FileLocator;
use RectorPrefix20211231\Symfony\Component\Config\Loader\DelegatingLoader;
use RectorPrefix20211231\Symfony\Component\Config\Loader\GlobFileLoader;
use RectorPrefix20211231\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20211231\Symfony\Component\Config\Loader\LoaderResolver;
use RectorPrefix20211231\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211231\Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface;
final class ConfigureCallMergingLoaderFactory implements \RectorPrefix20211231\Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface
{
    /**
     * @readonly
     * @var \Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector
     */
    private $configureCallValuesCollector;
    public function __construct(\Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector $configureCallValuesCollector)
    {
        $this->configureCallValuesCollector = $configureCallValuesCollector;
    }
    public function create(\RectorPrefix20211231\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20211231\Symfony\Component\Config\Loader\LoaderInterface
    {
        $fileLocator = new \RectorPrefix20211231\Symfony\Component\Config\FileLocator([$currentWorkingDirectory]);
        $loaderResolver = new \RectorPrefix20211231\Symfony\Component\Config\Loader\LoaderResolver([new \RectorPrefix20211231\Symfony\Component\Config\Loader\GlobFileLoader($fileLocator), new \Rector\Core\DependencyInjection\Loader\ConfigurableCallValuesCollectingPhpFileLoader($containerBuilder, $fileLocator, $this->configureCallValuesCollector)]);
        return new \RectorPrefix20211231\Symfony\Component\Config\Loader\DelegatingLoader($loaderResolver);
    }
}
