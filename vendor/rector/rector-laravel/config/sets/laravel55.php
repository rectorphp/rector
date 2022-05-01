<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameProperty;
# see: https://laravel.com/docs/5.5/upgrade
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Console\\Command', 'fire', 'handle')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector::class, [new \Rector\Renaming\ValueObject\RenameProperty('Illuminate\\Database\\Eloquent\\Concerns\\HasEvents', 'events', 'dispatchesEvents'), new \Rector\Renaming\ValueObject\RenameProperty('Illuminate\\Database\\Eloquent\\Relations\\Pivot', 'parent', 'pivotParent')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Illuminate\\Translation\\LoaderInterface' => 'Illuminate\\Contracts\\Translation\\Loader']);
};
