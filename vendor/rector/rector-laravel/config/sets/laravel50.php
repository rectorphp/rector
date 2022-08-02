<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# see: https://laravel.com/docs/5.0/upgrade
return static function (RectorConfig $rectorConfig) : void {
    # https://stackoverflow.com/a/24949656/1348344
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Illuminate\\Cache\\CacheManager' => 'Illuminate\\Contracts\\Cache\\Repository', 'Illuminate\\Database\\Eloquent\\SoftDeletingTrait' => 'Illuminate\\Database\\Eloquent\\SoftDeletes']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'links', 'render'), new MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getFrom', 'firstItem'), new MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getTo', 'lastItem'), new MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getPerPage', 'perPage'), new MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getCurrentPage', 'currentPage'), new MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getLastPage', 'lastPage'), new MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getTotal', 'total')]);
};
