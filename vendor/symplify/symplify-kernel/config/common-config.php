<?php

declare (strict_types=1);
namespace RectorPrefix20210519;

use RectorPrefix20210519\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210519\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210519\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20210519\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210519\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\SmartFileSystem\FileSystemFilter;
use Symplify\SmartFileSystem\FileSystemGuard;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\Finder\SmartFinder;
use Symplify\SmartFileSystem\SmartFileSystem;
use function RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    // symfony style
    $services->set(\RectorPrefix20210519\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class);
    $services->set(\RectorPrefix20210519\Symfony\Component\Console\Style\SymfonyStyle::class)->factory([\RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20210519\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class), 'create']);
    // filesystem
    $services->set(\Symplify\SmartFileSystem\Finder\FinderSanitizer::class);
    $services->set(\Symplify\SmartFileSystem\SmartFileSystem::class);
    $services->set(\Symplify\SmartFileSystem\Finder\SmartFinder::class);
    $services->set(\Symplify\SmartFileSystem\FileSystemGuard::class);
    $services->set(\Symplify\SmartFileSystem\FileSystemFilter::class);
    $services->set(\RectorPrefix20210519\Symplify\PackageBuilder\Parameter\ParameterProvider::class)->args([\RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20210519\Symfony\Component\DependencyInjection\ContainerInterface::class)]);
    $services->set(\RectorPrefix20210519\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
};
