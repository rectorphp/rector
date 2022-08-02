<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameProperty;
# see: https://laravel.com/docs/5.5/upgrade
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Illuminate\\Console\\Command', 'fire', 'handle')]);
    $rectorConfig->ruleWithConfiguration(RenamePropertyRector::class, [new RenameProperty('Illuminate\\Database\\Eloquent\\Concerns\\HasEvents', 'events', 'dispatchesEvents'), new RenameProperty('Illuminate\\Database\\Eloquent\\Relations\\Pivot', 'parent', 'pivotParent')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Illuminate\\Translation\\LoaderInterface' => 'Illuminate\\Contracts\\Translation\\Loader']);
};
