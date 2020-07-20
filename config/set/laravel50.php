<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see: https://laravel.com/docs/5.0/upgrade
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # https://stackoverflow.com/a/24949656/1348344
    $services->set(RenameClassRector::class)
        ->arg('$oldToNewClasses', [
            'Illuminate\Cache\CacheManager' => 'Illuminate\Contracts\Cache\Repository',
            'Illuminate\Database\Eloquent\SoftDeletingTrait' => 'Illuminate\Database\Eloquent\SoftDeletes',
        ]);

    $services->set(RenameMethodRector::class)
        ->arg('$oldToNewMethodsByClass', [
            'Illuminate\Contracts\Pagination\Paginator' => [
                'links' => 'render',
                'getFrom' => 'firstItem',
                'getTo' => 'lastItem',
                'getPerPage' => 'perPage',
                'getCurrentPage' => 'currentPage',
                'getLastPage' => 'lastPage',
                'getTotal' => 'total',
            ],
        ]);
};
