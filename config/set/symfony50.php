<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# https://github.com/symfony/symfony/blob/5.0/UPGRADE-5.0.md

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/symfony50-types.php');

    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            '$oldToNewClasses' => [
                'Symfony\Component\Debug\Debug' => 'Symfony\Component\ErrorHandler\Debug',
            ],
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            '$oldToNewMethodsByClass' => [
                'Symfony\Component\Console\Application' => [
                    'renderException' => 'renderThrowable',
                    'doRenderException' => 'doRenderThrowable',
                ],
            ],
        ]]);
};
