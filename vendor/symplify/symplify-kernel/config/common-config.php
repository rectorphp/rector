<?php

declare (strict_types=1);
namespace RectorPrefix20210818;

use RectorPrefix20210818\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210818\Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210818\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20210818\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210818\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix20210818\Symplify\SmartFileSystem\FileSystemFilter;
use RectorPrefix20210818\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20210818\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use RectorPrefix20210818\Symplify\SmartFileSystem\Finder\SmartFinder;
use RectorPrefix20210818\Symplify\SmartFileSystem\SmartFileSystem;
use function RectorPrefix20210818\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    // symfony style
    $services->set(\RectorPrefix20210818\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class);
    $services->set(\RectorPrefix20210818\Symfony\Component\Console\Style\SymfonyStyle::class)->factory([\RectorPrefix20210818\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20210818\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class), 'create']);
    // filesystem
    $services->set(\RectorPrefix20210818\Symplify\SmartFileSystem\Finder\FinderSanitizer::class);
    $services->set(\RectorPrefix20210818\Symplify\SmartFileSystem\SmartFileSystem::class);
    $services->set(\RectorPrefix20210818\Symplify\SmartFileSystem\Finder\SmartFinder::class);
    $services->set(\RectorPrefix20210818\Symplify\SmartFileSystem\FileSystemGuard::class);
    $services->set(\RectorPrefix20210818\Symplify\SmartFileSystem\FileSystemFilter::class);
    $services->set(\RectorPrefix20210818\Symplify\PackageBuilder\Parameter\ParameterProvider::class)->args([\RectorPrefix20210818\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20210818\Symfony\Component\DependencyInjection\ContainerInterface::class)]);
    $services->set(\RectorPrefix20210818\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
};
