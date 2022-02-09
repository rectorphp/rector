<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameProperty;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
# see: https://laravel.com/docs/5.5/upgrade
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Console\\Command', 'fire', 'handle')]);
    $services->set(\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector::class)->configure([new \Rector\Renaming\ValueObject\RenameProperty('Illuminate\\Database\\Eloquent\\Concerns\\HasEvents', 'events', 'dispatchesEvents'), new \Rector\Renaming\ValueObject\RenameProperty('Illuminate\\Database\\Eloquent\\Relations\\Pivot', 'parent', 'pivotParent')]);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['Illuminate\\Translation\\LoaderInterface' => 'Illuminate\\Contracts\\Translation\\Loader']);
};
