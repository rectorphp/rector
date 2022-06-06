<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use RectorPrefix20220606\Rector\Arguments\ValueObject\ArgumentAdder;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use RectorPrefix20220606\Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
# see https://laravel.com/docs/7.x/upgrade
return static function (RectorConfig $rectorConfig) : void {
    # https://github.com/laravel/framework/pull/30610/files
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'report', 0, new ObjectType('Throwable')), new AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'shouldReport', 0, new ObjectType('Throwable')), new AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'render', 1, new ObjectType('Throwable')), new AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'renderForConsole', 1, new ObjectType('Throwable'))]);
    # https://github.com/laravel/framework/pull/30471/files
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Illuminate\\Contracts\\Routing\\UrlRoutable', 'resolveRouteBinding', 1, 'field', null)]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        # https://github.com/laravel/framework/commit/aece7d78f3d28b2cdb63185dcc4a9b6092841310
        new MethodCallRename('Illuminate\\Support\\Facades\\Blade', 'component', 'aliasComponent'),
        # https://github.com/laravel/framework/pull/31463/files
        new MethodCallRename('Illuminate\\Database\\Eloquent\\Concerns\\HidesAttributes', 'addHidden', 'makeHidden'),
        # https://github.com/laravel/framework/pull/30348/files
        new MethodCallRename('Illuminate\\Database\\Eloquent\\Concerns\\HidesAttributes', 'addVisible', 'makeVisible'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/laravel/framework/pull/30619/files
        'Illuminate\\Http\\Resources\\Json\\Resource' => 'Illuminate\\Http\\Resources\\Json\\JsonResource',
        # https://github.com/laravel/framework/pull/31050/files
        'Illuminate\\Foundation\\Testing\\TestResponse' => 'Illuminate\\Testing\\TestResponse',
        'Illuminate\\Foundation\\Testing\\Assert' => 'Illuminate\\Testing\\Assert',
    ]);
};
