<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->arg('$oldToNewClasses', [
            # see: https://laravel.com/docs/5.0/upgrade
            'Illuminate\Cache\CacheManager' => 'Illuminate\Contracts\Cache\Repository',
            # https://stackoverflow.com/a/24949656/1348344
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
