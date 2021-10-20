<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use PHPStan\Type\ObjectType;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
# see https://laravel.com/docs/7.x/upgrade
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    # https://github.com/laravel/framework/pull/30610/files
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class)->call('configure', [[\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'report', 0, new \PHPStan\Type\ObjectType('Throwable')), new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'shouldReport', 0, new \PHPStan\Type\ObjectType('Throwable')), new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'render', 1, new \PHPStan\Type\ObjectType('Throwable')), new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Illuminate\\Contracts\\Debug\\ExceptionHandler', 'renderForConsole', 1, new \PHPStan\Type\ObjectType('Throwable'))])]]);
    # https://github.com/laravel/framework/pull/30471/files
    $services->set(\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector::class)->call('configure', [[\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector::ADDED_ARGUMENTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Arguments\ValueObject\ArgumentAdder('Illuminate\\Contracts\\Routing\\UrlRoutable', 'resolveRouteBinding', 1, 'field', null)])]]);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\MethodCall\RenameMethodRector::METHOD_CALL_RENAMES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
        # https://github.com/laravel/framework/commit/aece7d78f3d28b2cdb63185dcc4a9b6092841310
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Support\\Facades\\Blade', 'component', 'aliasComponent'),
        # https://github.com/laravel/framework/pull/31463/files
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Database\\Eloquent\\Concerns\\HidesAttributes', 'addHidden', 'makeHidden'),
        # https://github.com/laravel/framework/pull/30348/files
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Database\\Eloquent\\Concerns\\HidesAttributes', 'addVisible', 'makeVisible'),
    ])]]);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => [
        # https://github.com/laravel/framework/pull/30619/files
        'Illuminate\\Http\\Resources\\Json\\Resource' => 'Illuminate\\Http\\Resources\\Json\\JsonResource',
        # https://github.com/laravel/framework/pull/31050/files
        'Illuminate\\Foundation\\Testing\\TestResponse' => 'Illuminate\\Testing\\TestResponse',
        'Illuminate\\Foundation\\Testing\\Assert' => 'Illuminate\\Testing\\Assert',
    ]]]);
};
