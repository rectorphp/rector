<?php

declare(strict_types=1);

use Rector\Generic\Rector\Property\RenamePropertyRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see: https://laravel.com/docs/5.5/upgrade

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Illuminate\Console\Command' => [
                    'fire' => 'handle',
                ],
            ],
        ]]);

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::OLD_TO_NEW_PROPERTY_BY_TYPES => [
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
                RenameClassRector::OLD_TO_NEW_CLASSES => [
                    'Illuminate\Translation\LoaderInterface' => 'Illuminate\Contracts\Translation\Loader',
                ],
            ]]
        );
};
