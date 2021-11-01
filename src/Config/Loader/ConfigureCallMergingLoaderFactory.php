<?php

declare(strict_types=1);

namespace Rector\Core\Config\Loader;

use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use Rector\Core\DependencyInjection\Loader\ConfigurableCallValuesCollectingPhpFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\SymfonyContainerBuilder\Contract\Config\LoaderFactoryInterface;

final class ConfigureCallMergingLoaderFactory implements LoaderFactoryInterface
{
    public function __construct(
        private ConfigureCallValuesCollector $configureCallValuesCollector
    ) {
    }

    public function create(ContainerBuilder $containerBuilder, string $currentWorkingDirectory): LoaderInterface
    {
        $fileLocator = new FileLocator([$currentWorkingDirectory]);

        $loaderResolver = new LoaderResolver([
            new GlobFileLoader($fileLocator),
            new ConfigurableCallValuesCollectingPhpFileLoader(
                $containerBuilder,
                $fileLocator,
                $this->configureCallValuesCollector
            ),
        ]);

        return new DelegatingLoader($loaderResolver);
    }
}
