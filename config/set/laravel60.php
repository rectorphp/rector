<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\Rector\Expression\MethodCallToReturnRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\Generic\ValueObject\ChangeMethodVisibility;
use Rector\Generic\ValueObject\MethodCallToReturn;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see https://laravel.com/docs/6.x/upgrade
# https://github.com/laravel/docs/pull/5531/files

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToReturnRector::class)
        ->call('configure', [[
            MethodCallToReturnRector::METHOD_CALL_WRAPS => inline_value_objects([
                new MethodCallToReturn('Illuminate\Auth\Access\HandlesAuthorization', 'deny'),
            ]),
        ]]);

    # https://github.com/laravel/framework/commit/67a38ba0fa2acfbd1f4af4bf7d462bb4419cc091
    $services->set(ParamTypeDeclarationRector::class);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename(
                    'Illuminate\Auth\Access\Gate',
                    # https://github.com/laravel/framework/commit/69de466ddc25966a0f6551f48acab1afa7bb9424
                    'access',
                    'inspect'
                ),
                new MethodCallRename(
                    'Illuminate\Support\Facades\Lang',
                    # https://github.com/laravel/framework/commit/efbe23c4116f86846ad6edc0d95cd56f4175a446
                    'trans',
                    'get'
                ),
                new MethodCallRename('Illuminate\Support\Facades\Lang', 'transChoice', 'choice'),
                new MethodCallRename(
                    'Illuminate\Translation\Translator',
                    # https://github.com/laravel/framework/commit/697b898a1c89881c91af83ecc4493fa681e2aa38
                    'getFromJson',
                    'get'
                ),
            ]),
        ]]);

    $configuration = [
        # https://github.com/laravel/framework/commit/55785d3514a8149d4858acef40c56a31b6b2ccd1
        new RenameStaticMethod(
            'Illuminate\Support\Facades\Input',
            'get',
            'Illuminate\Support\Facades\Request',
            'input'
        ),
    ];

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => inline_value_objects($configuration),
        ]]);

    $services->set(RenameClassRector::class)
        ->call(
            'configure',
            [[
                RenameClassRector::OLD_TO_NEW_CLASSES => [
                    'Illuminate\Support\Facades\Input' => 'Illuminate\Support\Facades\Request',
                ],
            ]]
        );

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => inline_value_objects([
                new ChangeMethodVisibility('Illuminate\Foundation\Http\FormRequest', 'validationData', 'public'),
            ]),
        ]]);

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::ADDED_ARGUMENTS => inline_value_objects([
                // https://github.com/laravel/framework/commit/6c1e014943a508afb2c10869c3175f7783a004e1
                new ArgumentAdder('Illuminate\Database\Capsule\Manager', 'table', 1, 'as', 'null'),
                new ArgumentAdder('Illuminate\Database\Connection', 'table', 1, 'as', 'null'),
                new ArgumentAdder('Illuminate\Database\ConnectionInterface', 'table', 1, 'as', 'null'),
                new ArgumentAdder('Illuminate\Database\Query\Builder', 'from', 1, 'as', 'null'),
            ]),
        ]]);
};
