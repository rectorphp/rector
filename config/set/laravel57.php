<?php

declare(strict_types=1);

use Rector\Core\ValueObject\Visibility;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector;
use Rector\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector;
use Rector\Laravel\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector;
use Rector\Laravel\Rector\New_\AddGuardToLoginEventRector;
use Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# see: https://laravel.com/docs/5.7/upgrade
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ChangeMethodVisibilityRector::class)->call('configure', [[
        ChangeMethodVisibilityRector::METHOD_VISIBILITIES => ValueObjectInliner::inline([
            new ChangeMethodVisibility('Illuminate\Routing\Router', 'addRoute', Visibility::PUBLIC),
            new ChangeMethodVisibility('Illuminate\Contracts\Auth\Access\Gate', 'raw', Visibility::PUBLIC),
        ]),
    ]]);
    $services->set(ArgumentAdderRector::class)->call('configure', [[
        ArgumentAdderRector::ADDED_ARGUMENTS => ValueObjectInliner::inline([
            new ArgumentAdder('Illuminate\Auth\Middleware\Authenticate', 'authenticate', 0, 'request'),
            new ArgumentAdder(
                'Illuminate\Foundation\Auth\ResetsPasswords',
                'sendResetResponse',
                0,
                'request',
                null,
                'Illuminate\Http\Illuminate\Http'
            ),
            new ArgumentAdder(
                'Illuminate\Foundation\Auth\SendsPasswordResetEmails',
                'sendResetLinkResponse',
                0,
                'request',
                null,
                'Illuminate\Http\Illuminate\Http'
            ),
            new ArgumentAdder('Illuminate\Database\ConnectionInterface', 'select', 2, 'useReadPdo', true),
            new ArgumentAdder('Illuminate\Database\ConnectionInterface', 'selectOne', 2, 'useReadPdo', true),
        ]),
    ]]);
    $services->set(Redirect301ToPermanentRedirectRector::class);
    $services->set(ArgumentRemoverRector::class)->call('configure', [[
        ArgumentRemoverRector::REMOVED_ARGUMENTS => ValueObjectInliner::inline([
            new ArgumentRemover('Illuminate\Foundation\Application', 'register', 1, [
                'name' => 'options',
            ]), ]
        ),
    ]]);
    $services->set(AddParentBootToModelClassMethodRector::class);
    $services->set(ChangeQueryWhereDateValueWithCarbonRector::class);
    $services->set(AddMockConsoleOutputFalseToConsoleTestsRector::class);
    $services->set(AddGuardToLoginEventRector::class);
};
