<?php

declare(strict_types=1);

use Rector\Core\ValueObject\Visibility;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\Transform\Rector\Expression\MethodCallToReturnRector;
use Rector\Transform\ValueObject\MethodCallToReturn;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# see https://laravel.com/docs/6.x/upgrade
# https://github.com/laravel/docs/pull/5531/files

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToReturnRector::class)
        ->call('configure', [[
            MethodCallToReturnRector::METHOD_CALL_WRAPS => ValueObjectInliner::inline([
                new MethodCallToReturn('Illuminate\Auth\Access\HandlesAuthorization', 'deny'),
            ]),
        ]]);

    # https://github.com/laravel/framework/commit/67a38ba0fa2acfbd1f4af4bf7d462bb4419cc091
    $services->set(ParamTypeDeclarationRector::class);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
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
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => ValueObjectInliner::inline($configuration),
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
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => ValueObjectInliner::inline([
                new ChangeMethodVisibility(
                    'Illuminate\Foundation\Http\FormRequest',
                    'validationData',
                    Visibility::PUBLIC
                ),
            ]),
        ]]);

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::ADDED_ARGUMENTS => ValueObjectInliner::inline([
                // https://github.com/laravel/framework/commit/6c1e014943a508afb2c10869c3175f7783a004e1
                new ArgumentAdder('Illuminate\Database\Capsule\Manager', 'table', 1, 'as', 'null'),
                new ArgumentAdder('Illuminate\Database\Connection', 'table', 1, 'as', 'null'),
                new ArgumentAdder('Illuminate\Database\ConnectionInterface', 'table', 1, 'as', 'null'),
                new ArgumentAdder('Illuminate\Database\Query\Builder', 'from', 1, 'as', 'null'),
            ]),
        ]]);
};
