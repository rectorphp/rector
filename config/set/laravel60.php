<?php

declare(strict_types=1);

use Rector\Generic\Rector\Argument\ArgumentAdderRector;
use Rector\Generic\Rector\MethodCall\MethodCallToReturnRector;
use Rector\Generic\Rector\Visibility\ChangeMethodVisibilityRector;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see https://laravel.com/docs/6.x/upgrade
# https://github.com/laravel/docs/pull/5531/files

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToReturnRector::class)
        ->call('configure', [[
            MethodCallToReturnRector::METHOD_NAMES_BY_TYPE => [
                'Illuminate\Auth\Access\HandlesAuthorization' => ['deny'],
            ],
        ]]);

    # https://github.com/laravel/framework/commit/67a38ba0fa2acfbd1f4af4bf7d462bb4419cc091
    $services->set(ParamTypeDeclarationRector::class);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Illuminate\Auth\Access\Gate' => [
                    # https://github.com/laravel/framework/commit/69de466ddc25966a0f6551f48acab1afa7bb9424
                    'access' => 'inspect',
                ],
                'Illuminate\Support\Facades\Lang' => [
                    # https://github.com/laravel/framework/commit/efbe23c4116f86846ad6edc0d95cd56f4175a446
                    'trans' => 'get',
                    'transChoice' => 'choice',
                ],
                'Illuminate\Translation\Translator' => [
                    # https://github.com/laravel/framework/commit/697b898a1c89881c91af83ecc4493fa681e2aa38
                    'getFromJson' => 'get',
                ],
            ],
        ]]);

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => [
                'Illuminate\Support\Facades\Input' => [
                    'get' => [
                        # https://github.com/laravel/framework/commit/55785d3514a8149d4858acef40c56a31b6b2ccd1
                        'Illuminate\Support\Facades\Request',
                        'input',
                    ],
                ],
            ],
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
            ChangeMethodVisibilityRector::METHOD_TO_VISIBILITY_BY_CLASS => [
                'Illuminate\Foundation\Http\FormRequest' => [
                    # https://github.com/laravel/framework/commit/e47e91417ab22e6af001db1dcbe75b87db218c1d
                    'validationData' => 'public',
                ],
            ],
        ]]);

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::POSITION_WITH_DEFAULT_VALUE_BY_METHOD_NAMES_BY_CLASS_TYPES => [
                'Illuminate\Database\Capsule\Manager' => [
                    'table' => [
                        1 => [
                            # https://github.com/laravel/framework/commit/6c1e014943a508afb2c10869c3175f7783a004e1
                            'name' => 'as',
                            'default_value' => 'null',
                        ],
                    ],
                ],
                'Illuminate\Database\Connection' => [
                    'table' => [
                        1 => [
                            'name' => 'as',
                            'default_value' => 'null',
                        ],
                    ],
                ],
                'Illuminate\Database\ConnectionInterface' => [
                    'table' => [
                        1 => [
                            'name' => 'as',
                            'default_value' => 'null',
                        ],
                    ],
                ],
                'Illuminate\Database\Query\Builder' => [
                    'from' => [
                        1 => [
                            'name' => 'as',
                            'default_value' => 'null',
                        ],
                    ],
                ],
            ],
        ]]);
};
