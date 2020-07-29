<?php

declare(strict_types=1);

use Rector\Generic\Rector\Property\RenamePropertyRector;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see: https://laravel.com/docs/5.5/upgrade

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            '$oldToNewMethodsByClass' => [
                'Illuminate\Console\Command' => [
                    'fire' => 'handle',
                ],
            ],
        ]]);

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            '$oldToNewPropertyByTypes' => [
                'Illuminate\Database\Eloquent\Concerns\HasEvents' => [
                    'events' => 'dispatchesEvents',
                ],
                'Illuminate\Database\Eloquent\Relations\Pivot' => [
                    'parent' => 'pivotParent',
                ],
            ],
        ]]);

    $services->set(RenameClassRector::class)
        ->call(
            'configure',
            [[
                '$oldToNewClasses' => [
                    'Illuminate\Translation\LoaderInterface' => 'Illuminate\Contracts\Translation\Loader',
                ],
            ]]
        );
};
