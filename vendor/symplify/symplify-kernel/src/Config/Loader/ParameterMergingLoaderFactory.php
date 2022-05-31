<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Symplify\SymplifyKernel\Config\Loader;

use RectorPrefix20220531\Symfony\Component\Config\FileLocator;
use RectorPrefix20220531\Symfony\Component\Config\Loader\DelegatingLoader;
use RectorPrefix20220531\Symfony\Component\Config\Loader\GlobFileLoader;
use RectorPrefix20220531\Symfony\Component\Config\Loader\LoaderResolver;
use RectorPrefix20220531\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220531\Symplify\PackageBuilder\DependencyInjection\FileLoader\ParameterMergingPhpFileLoader;
use RectorPrefix20220531\Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface;
final class ParameterMergingLoaderFactory implements \RectorPrefix20220531\Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface
{
    public function create(\RectorPrefix20220531\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20220531\Symfony\Component\Config\Loader\LoaderInterface
    {
        $fileLocator = new \RectorPrefix20220531\Symfony\Component\Config\FileLocator([$currentWorkingDirectory]);
        $loaders = [new \RectorPrefix20220531\Symfony\Component\Config\Loader\GlobFileLoader($fileLocator), new \RectorPrefix20220531\Symplify\PackageBuilder\DependencyInjection\FileLoader\ParameterMergingPhpFileLoader($containerBuilder, $fileLocator)];
        $loaderResolver = new \RectorPrefix20220531\Symfony\Component\Config\Loader\LoaderResolver($loaders);
        return new \RectorPrefix20220531\Symfony\Component\Config\Loader\DelegatingLoader($loaderResolver);
    }
}
