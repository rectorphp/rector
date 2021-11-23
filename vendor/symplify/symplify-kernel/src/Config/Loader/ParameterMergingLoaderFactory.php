<?php

declare (strict_types=1);
namespace RectorPrefix20211123\Symplify\SymplifyKernel\Config\Loader;

use RectorPrefix20211123\Symfony\Component\Config\FileLocator;
use RectorPrefix20211123\Symfony\Component\Config\Loader\DelegatingLoader;
use RectorPrefix20211123\Symfony\Component\Config\Loader\GlobFileLoader;
use RectorPrefix20211123\Symfony\Component\Config\Loader\LoaderResolver;
use RectorPrefix20211123\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211123\Symplify\PackageBuilder\DependencyInjection\FileLoader\ParameterMergingPhpFileLoader;
use RectorPrefix20211123\Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface;
final class ParameterMergingLoaderFactory implements \RectorPrefix20211123\Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     * @param string $currentWorkingDirectory
     */
    public function create($containerBuilder, $currentWorkingDirectory) : \RectorPrefix20211123\Symfony\Component\Config\Loader\LoaderInterface
    {
        $fileLocator = new \RectorPrefix20211123\Symfony\Component\Config\FileLocator([$currentWorkingDirectory]);
        $loaders = [new \RectorPrefix20211123\Symfony\Component\Config\Loader\GlobFileLoader($fileLocator), new \RectorPrefix20211123\Symplify\PackageBuilder\DependencyInjection\FileLoader\ParameterMergingPhpFileLoader($containerBuilder, $fileLocator)];
        $loaderResolver = new \RectorPrefix20211123\Symfony\Component\Config\Loader\LoaderResolver($loaders);
        return new \RectorPrefix20211123\Symfony\Component\Config\Loader\DelegatingLoader($loaderResolver);
    }
}
