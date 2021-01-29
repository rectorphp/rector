<?php

declare(strict_types=1);

use Rector\Core\ValueObject\Visibility;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# see: https://laravel.com/docs/5.6/upgrade

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename(
                    'Illuminate\Validation\ValidatesWhenResolvedTrait',
                    'validate',
                    'validateResolved'
                ),
                new MethodCallRename(
                    'Illuminate\Contracts\Validation\ValidatesWhenResolved',
                    'validate',
                    'validateResolved'
                ),
            ]),
        ]]);

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => ValueObjectInliner::inline([
                new ChangeMethodVisibility('Illuminate\Routing\Router', 'addRoute', Visibility::PUBLIC),
                new ChangeMethodVisibility('Illuminate\Contracts\Auth\Access\Gate', 'raw', Visibility::PUBLIC),
                new ChangeMethodVisibility('Illuminate\Database\Grammar', 'getDateFormat', Visibility::PUBLIC),
            ]),
        ]]);
};
