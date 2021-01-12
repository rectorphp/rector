<?php

declare(strict_types=1);

use Rector\RectorGenerator\Rector\Closure\AddNewServiceToSymfonyPhpConfigRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symplify\SmartFileSystem\FileSystemGuard;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure()
        ->bind(AddNewServiceToSymfonyPhpConfigRector::class, service(AddNewServiceToSymfonyPhpConfigRector::class));

    $services->load('Rector\RectorGenerator\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Exception', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/Rector']);

    $services->set(AddNewServiceToSymfonyPhpConfigRector::class)
        ->autowire(false);

    $services->set(FileSystemGuard::class);
};
