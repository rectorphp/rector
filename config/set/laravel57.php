<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\ValueObject\AddedArgument;
use Rector\Generic\ValueObject\MethodVisibility;
use Rector\Generic\ValueObject\RemovedArgument;
use Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see: https://laravel.com/docs/5.7/upgrade

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => inline_value_objects([
                new MethodVisibility('Illuminate\Routing\Router', 'addRoute', 'public'),
                new MethodVisibility('Illuminate\Contracts\Auth\Access\Gate', 'raw', 'public'),
            ]),
        ]]);

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::ADDED_ARGUMENTS => inline_value_objects([
                new AddedArgument('Illuminate\Auth\Middleware\Authenticate', 'authenticate', 0, 'request'),
                new AddedArgument(
                    'Illuminate\Foundation\Auth\ResetsPasswords',
                    'sendResetResponse',
                    0,
                    'request',
                    null,
                    'Illuminate\Http\Illuminate\Http'
                ),
                new AddedArgument(
                    'Illuminate\Foundation\Auth\SendsPasswordResetEmails',
                    'sendResetLinkResponse',
                    0,
                    'request',
                    null,
                    'Illuminate\Http\Illuminate\Http'
                ),
            ]),
        ]]);

    $services->set(Redirect301ToPermanentRedirectRector::class);

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            ArgumentRemoverRector::REMOVED_ARGUMENTS => inline_value_objects([
                new RemovedArgument('Illuminate\Foundation\Application', 'register', 1, ['name' => 'options']),
            ]),
        ]]);
};
