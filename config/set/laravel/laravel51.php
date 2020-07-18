<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Class_\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->arg('$oldToNewClasses', [
            # see: https://laravel.com/docs/5.1/upgrade
            'Illuminate\Validation\Validator' => 'Illuminate\Contracts\Validation\Validator',
        ]);
};
