<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\Visibility;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
use RectorPrefix20220606\Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use RectorPrefix20220606\Rector\Visibility\ValueObject\ChangeMethodVisibility;
# see: https://laravel.com/docs/5.6/upgrade
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Illuminate\\Validation\\ValidatesWhenResolvedTrait', 'validate', 'validateResolved'), new MethodCallRename('Illuminate\\Contracts\\Validation\\ValidatesWhenResolved', 'validate', 'validateResolved')]);
    $rectorConfig->ruleWithConfiguration(ChangeMethodVisibilityRector::class, [new ChangeMethodVisibility('Illuminate\\Routing\\Router', 'addRoute', Visibility::PUBLIC), new ChangeMethodVisibility('Illuminate\\Contracts\\Auth\\Access\\Gate', 'raw', Visibility::PUBLIC), new ChangeMethodVisibility('Illuminate\\Database\\Grammar', 'getDateFormat', Visibility::PUBLIC)]);
};
