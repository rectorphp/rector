<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# see: https://laravel.com/docs/5.0/upgrade
return static function (RectorConfig $rectorConfig) : void {
    # https://stackoverflow.com/a/24949656/1348344
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Illuminate\\Cache\\CacheManager' => 'RectorPrefix20220607\\Illuminate\\Contracts\\Cache\\Repository', 'RectorPrefix20220607\\Illuminate\\Database\\Eloquent\\SoftDeletingTrait' => 'RectorPrefix20220607\\Illuminate\\Database\\Eloquent\\SoftDeletes']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Illuminate\\Contracts\\Pagination\\Paginator', 'links', 'render'), new MethodCallRename('RectorPrefix20220607\\Illuminate\\Contracts\\Pagination\\Paginator', 'getFrom', 'firstItem'), new MethodCallRename('RectorPrefix20220607\\Illuminate\\Contracts\\Pagination\\Paginator', 'getTo', 'lastItem'), new MethodCallRename('RectorPrefix20220607\\Illuminate\\Contracts\\Pagination\\Paginator', 'getPerPage', 'perPage'), new MethodCallRename('RectorPrefix20220607\\Illuminate\\Contracts\\Pagination\\Paginator', 'getCurrentPage', 'currentPage'), new MethodCallRename('RectorPrefix20220607\\Illuminate\\Contracts\\Pagination\\Paginator', 'getLastPage', 'lastPage'), new MethodCallRename('RectorPrefix20220607\\Illuminate\\Contracts\\Pagination\\Paginator', 'getTotal', 'total')]);
};
