<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use RectorPrefix20210510\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210510\Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use RectorPrefix20210510\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;
use RectorPrefix20210510\Symplify\ComposerJsonManipulator\Json\JsonCleaner;
use RectorPrefix20210510\Symplify\ComposerJsonManipulator\Json\JsonInliner;
use RectorPrefix20210510\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20210510\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210510\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix20210510\Symplify\SmartFileSystem\FileSystemFilter;
use RectorPrefix20210510\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20210510\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use RectorPrefix20210510\Symplify\SmartFileSystem\Finder\SmartFinder;
use RectorPrefix20210510\Symplify\SmartFileSystem\SmartFileSystem;
use RectorPrefix20210510\Symplify\SymplifyKernel\Console\ConsoleApplicationFactory;
use function RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    // symfony style
    $services->set(SymfonyStyleFactory::class);
    $services->set(SymfonyStyle::class)->factory([service(SymfonyStyleFactory::class), 'create']);
    // filesystem
    $services->set(FinderSanitizer::class);
    $services->set(SmartFileSystem::class);
    $services->set(SmartFinder::class);
    $services->set(FileSystemGuard::class);
    $services->set(FileSystemFilter::class);
    $services->set(ParameterProvider::class)->args([service(ContainerInterface::class)]);
    $services->set(PrivatesAccessor::class);
    $services->set(ConsoleApplicationFactory::class);
    // composer json factory
    $services->set(ComposerJsonFactory::class);
    $services->set(JsonFileManager::class);
    $services->set(JsonCleaner::class);
    $services->set(JsonInliner::class);
};
