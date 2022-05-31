<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use PHPStan\Type\ObjectType;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
# see https://laravel.com/docs/7.x/upgrade
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    # https://github.com/laravel/framework/pull/30610/files
    $rectorConfig->ruleWithConfiguration(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class, [new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'report', 0, new \PHPStan\Type\ObjectType('Throwable')), new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'shouldReport', 0, new \PHPStan\Type\ObjectType('Throwable')), new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'render', 1, new \PHPStan\Type\ObjectType('Throwable')), new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'renderForConsole', 1, new \PHPStan\Type\ObjectType('Throwable'))]);
    # https://github.com/laravel/framework/pull/30471/files
    $rectorConfig->ruleWithConfiguration(\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector::class, [new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Contracts\\Routing\\UrlRoutable', 'resolveRouteBinding', 1, 'field', null)]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [
        # https://github.com/laravel/framework/commit/aece7d78f3d28b2cdb63185dcc4a9b6092841310
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Support\\Facades\\Blade', 'component', 'aliasComponent'),
        # https://github.com/laravel/framework/pull/31463/files
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Database\\Eloquent\\Concerns\\HidesAttributes', 'addHidden', 'makeHidden'),
        # https://github.com/laravel/framework/pull/30348/files
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Database\\Eloquent\\Concerns\\HidesAttributes', 'addVisible', 'makeVisible'),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, [
        # https://github.com/laravel/framework/pull/30619/files
        'Illuminate\\Http\\Resources\\Json\\Resource' => 'Illuminate\\Http\\Resources\\Json\\JsonResource',
        # https://github.com/laravel/framework/pull/31050/files
        'Illuminate\\Foundation\\Testing\\TestResponse' => 'Illuminate\\Testing\\TestResponse',
        'Illuminate\\Foundation\\Testing\\Assert' => 'Illuminate\\Testing\\Assert',
    ]);
};
