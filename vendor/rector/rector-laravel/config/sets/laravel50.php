<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# see: https://laravel.com/docs/5.0/upgrade
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    # https://stackoverflow.com/a/24949656/1348344
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Illuminate\\Cache\\CacheManager' => 'Illuminate\\Contracts\\Cache\\Repository', 'Illuminate\\Database\\Eloquent\\SoftDeletingTrait' => 'Illuminate\\Database\\Eloquent\\SoftDeletes']);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'links', 'render'), new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getFrom', 'firstItem'), new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getTo', 'lastItem'), new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getPerPage', 'perPage'), new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getCurrentPage', 'currentPage'), new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getLastPage', 'lastPage'), new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Pagination\\Paginator', 'getTotal', 'total')]);
};
