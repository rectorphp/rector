<?php

declare(strict_types=1);

use Rector\Generic\Rector\Argument\ArgumentAdderRector;
use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see: https://laravel.com/docs/5.7/upgrade

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_TO_VISIBILITY_BY_CLASS => [
                'Illuminate\Routing\Router' => [
                    'addRoute' => 'public',
                ],
                'Illuminate\Contracts\Auth\Access\Gate' => [
                    'raw' => 'public',
                ],
            ],
        ]]);

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::POSITION_WITH_DEFAULT_VALUE_BY_METHOD_NAMES_BY_CLASS_TYPES => [
                'Illuminate\Auth\Middleware\Authenticate' => [
                    'authenticate' => [
                        'name' => 'request',
                    ],
                ],
                'Illuminate\Foundation\Auth\ResetsPasswords' => [
                    'sendResetResponse' => [
                        'name' => 'request',
                        'type' => 'Illuminate\Http\Illuminate\Http',
                    ],
                ],
                'Illuminate\Foundation\Auth\SendsPasswordResetEmails' => [
                    'sendResetLinkResponse' => [
                        'name' => 'request',
                        'type' => 'Illuminate\Http\Illuminate\Http',
                    ],
                ],
            ],
        ]]);

    $services->set(Redirect301ToPermanentRedirectRector::class);

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            ArgumentRemoverRector::POSITIONS_BY_METHOD_NAME_BY_CLASS_TYPE => [
                'Illuminate\Foundation\Application' => [
                    'register' => [
                        1 => [
                            'name' => 'options',
                        ],
                    ],
                ],
            ],
        ]]);
};
