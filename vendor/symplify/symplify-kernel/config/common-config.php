<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use RectorPrefix202208\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202208\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix202208\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix202208\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix202208\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix202208\Symplify\SmartFileSystem\FileSystemFilter;
use RectorPrefix202208\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix202208\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use RectorPrefix202208\Symplify\SmartFileSystem\Finder\SmartFinder;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
use function RectorPrefix202208\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    // symfony style
    $services->set(SymfonyStyleFactory::class);
    $services->set(SymfonyStyle::class)->factory([service(SymfonyStyleFactory::class), 'create']);
    // filesystem
    $services->set(FinderSanitizer::class);
    $services->set(SmartFileSystem::class);
    $services->set(SmartFinder::class);
    $services->set(FileSystemGuard::class);
    $services->set(FileSystemFilter::class);
    $services->set(ParameterProvider::class)->args([service('service_container')]);
    $services->set(PrivatesAccessor::class);
};
