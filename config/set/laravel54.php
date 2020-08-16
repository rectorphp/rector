<?php

declare(strict_types=1);

use Rector\Generic\Rector\String_\StringToClassConstantRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see: https://laravel.com/docs/5.4/upgrade

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringToClassConstantRector::class)
        ->call('configure', [[
            StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => [
                'kernel.handled' => ['Illuminate\Foundation\Http\Events\RequestHandled', 'class'],
                'locale.changed' => ['Illuminate\Foundation\Events\LocaleUpdated', 'class'],
                'illuminate.log' => ['Illuminate\Log\Events\MessageLogged', 'class'],
            ],
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Illuminate\Console\AppNamespaceDetectorTrait' => 'Illuminate\Console\DetectsApplicationNamespace',
                'Illuminate\Http\Exception\HttpResponseException' => 'Illuminate\Http\Exceptions\HttpResponseException',
                'Illuminate\Http\Exception\PostTooLargeException' => 'Illuminate\Http\Exceptions\PostTooLargeException',
                'Illuminate\Foundation\Http\Middleware\VerifyPostSize' => 'Illuminate\Foundation\Http\Middleware\ValidatePostSize',
                'Symfony\Component\HttpFoundation\Session\SessionInterface' => 'Illuminate\Contracts\Session\Session',
            ],
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Illuminate\Support\Collection' => [
                    'every' => 'nth',
                ],
                'Illuminate\Database\Eloquent\Relations\BelongsToMany' => [
                    'setJoin' => 'performJoin',
                    'getRelatedIds' => 'allRelatedIds',
                ],
                'Illuminate\Routing\Router' => [
                    'middleware' => 'aliasMiddleware',
                ],
                'Illuminate\Routing\Route' => [
                    'getPath' => 'uri',
                    'getUri' => 'uri',
                    'getMethods' => 'methods',
                    'getParameter' => 'parameter',
                ],
                'Illuminate\Contracts\Session\Session' => [
                    'set' => 'put',
                    'getToken' => 'token',
                ],
                'Illuminate\Support\Facades\Request' => [
                    'setSession' => 'setLaravelSession',
                ],
                'Illuminate\Http\Request' => [
                    'setSession' => 'setLaravelSession',
                ],
                'Illuminate\Routing\UrlGenerator' => [
                    'forceSchema' => 'forceScheme',
                ],
                'Illuminate\Validation\Validator' => [
                    'addError' => 'addFailure',
                    'doReplacements' => 'makeReplacements',
                ],
            ],
        ]]);
};
