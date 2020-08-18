<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see: https://laravel.com/docs/5.0/upgrade
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # https://stackoverflow.com/a/24949656/1348344
    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Illuminate\Cache\CacheManager' => 'Illuminate\Contracts\Cache\Repository',
                'Illuminate\Database\Eloquent\SoftDeletingTrait' => 'Illuminate\Database\Eloquent\SoftDeletes',
            ],
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Illuminate\Contracts\Pagination\Paginator' => [
                    'links' => 'render',
                    'getFrom' => 'firstItem',
                    'getTo' => 'lastItem',
                    'getPerPage' => 'perPage',
                    'getCurrentPage' => 'currentPage',
                    'getLastPage' => 'lastPage',
                    'getTotal' => 'total',
                ],
            ],
        ]]);
};
