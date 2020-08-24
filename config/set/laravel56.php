<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see: https://laravel.com/docs/5.6/upgrade

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
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
            ChangeMethodVisibilityRector::METHOD_TO_VISIBILITY_BY_CLASS => [
                'Illuminate\Routing\Router' => [
                    'addRoute' => 'public',
                ],
                'Illuminate\Contracts\Auth\Access\Gate' => [
                    'raw' => 'public',
                ],
                'Illuminate\Database\Grammar' => [
                    'getDateFormat' => 'public',
                ],
            ],
        ]]);
};
