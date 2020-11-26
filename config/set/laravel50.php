<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

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
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Illuminate\Contracts\Pagination\Paginator', 'links', 'render'),
                new MethodCallRename('Illuminate\Contracts\Pagination\Paginator', 'getFrom', 'firstItem'),
                new MethodCallRename('Illuminate\Contracts\Pagination\Paginator', 'getTo', 'lastItem'),
                new MethodCallRename('Illuminate\Contracts\Pagination\Paginator', 'getPerPage', 'perPage'),
                new MethodCallRename('Illuminate\Contracts\Pagination\Paginator', 'getCurrentPage', 'currentPage'),
                new MethodCallRename('Illuminate\Contracts\Pagination\Paginator', 'getLastPage', 'lastPage'),
                new MethodCallRename('Illuminate\Contracts\Pagination\Paginator', 'getTotal', 'total'),
            ]),
        ]]);
};
