<?php

declare(strict_types=1);

use OndraM\CiDetector\CiDetector;
use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('dataDir', __DIR__ . '/../build');

    $parameters->set('buildDir', __DIR__ . '/../..');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire();

    $services->load('Rector\Compiler\\', __DIR__ . '/../src')
        ->exclude([
            __DIR__ . '/../src/Exception/*',
            __DIR__ . '/../src/DependencyInjection/*',
            __DIR__ . '/../src/HttpKernel/*',
            __DIR__ . '/../src/PhpScoper/*',
        ]);

    $services->set(Filesystem::class);

    $services->set(CiDetector::class);
};
