<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\Visibility;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
# see: https://laravel.com/docs/5.6/upgrade
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Illuminate\\Validation\\ValidatesWhenResolvedTrait', 'validate', 'validateResolved'), new MethodCallRename('Illuminate\\Contracts\\Validation\\ValidatesWhenResolved', 'validate', 'validateResolved')]);
    $rectorConfig->ruleWithConfiguration(ChangeMethodVisibilityRector::class, [new ChangeMethodVisibility('Illuminate\\Routing\\Router', 'addRoute', Visibility::PUBLIC), new ChangeMethodVisibility('Illuminate\\Contracts\\Auth\\Access\\Gate', 'raw', Visibility::PUBLIC), new ChangeMethodVisibility('Illuminate\\Database\\Grammar', 'getDateFormat', Visibility::PUBLIC)]);
};
