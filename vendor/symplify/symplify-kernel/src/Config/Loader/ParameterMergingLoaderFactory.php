<?php

declare (strict_types=1);
namespace RectorPrefix202206\Symplify\SymplifyKernel\Config\Loader;

use RectorPrefix202206\Symfony\Component\Config\FileLocator;
use RectorPrefix202206\Symfony\Component\Config\Loader\DelegatingLoader;
use RectorPrefix202206\Symfony\Component\Config\Loader\GlobFileLoader;
use RectorPrefix202206\Symfony\Component\Config\Loader\LoaderResolver;
use RectorPrefix202206\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix202206\Symplify\PackageBuilder\DependencyInjection\FileLoader\ParameterMergingPhpFileLoader;
use RectorPrefix202206\Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface;
final class ParameterMergingLoaderFactory implements LoaderFactoryInterface
{
    public function create(ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix202206\Symfony\Component\Config\Loader\LoaderInterface
    {
        $fileLocator = new FileLocator([$currentWorkingDirectory]);
        $loaders = [new GlobFileLoader($fileLocator), new ParameterMergingPhpFileLoader($containerBuilder, $fileLocator)];
        $loaderResolver = new LoaderResolver($loaders);
        return new DelegatingLoader($loaderResolver);
    }
}
