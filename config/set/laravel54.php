<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# see: https://laravel.com/docs/5.4/upgrade

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringToClassConstantRector::class)
        ->call('configure', [[
            StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => ValueObjectInliner::inline([
                new StringToClassConstant(
                    'kernel.handled',
                    'Illuminate\Foundation\Http\Events\RequestHandled',
                    'class'
                ),
                new StringToClassConstant('locale.changed', 'Illuminate\Foundation\Events\LocaleUpdated', 'class'),
                new StringToClassConstant('illuminate.log', 'Illuminate\Log\Events\MessageLogged', 'class'),
            ]),
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
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Illuminate\Support\Collection', 'every', 'nth'),
                new MethodCallRename(
                    'Illuminate\Database\Eloquent\Relations\BelongsToMany',
                    'setJoin',
                    'performJoin'
                ),
                new MethodCallRename(
                    'Illuminate\Database\Eloquent\Relations\BelongsToMany',
                    'getRelatedIds',
                    'allRelatedIds'
                ),
                new MethodCallRename('Illuminate\Routing\Router', 'middleware', 'aliasMiddleware'),
                new MethodCallRename('Illuminate\Routing\Route', 'getPath', 'uri'),
                new MethodCallRename('Illuminate\Routing\Route', 'getUri', 'uri'),
                new MethodCallRename('Illuminate\Routing\Route', 'getMethods', 'methods'),
                new MethodCallRename('Illuminate\Routing\Route', 'getParameter', 'parameter'),
                new MethodCallRename('Illuminate\Contracts\Session\Session', 'set', 'put'),
                new MethodCallRename('Illuminate\Contracts\Session\Session', 'getToken', 'token'),
                new MethodCallRename('Illuminate\Support\Facades\Request', 'setSession', 'setLaravelSession'),
                new MethodCallRename('Illuminate\Http\Request', 'setSession', 'setLaravelSession'),
                new MethodCallRename('Illuminate\Routing\UrlGenerator', 'forceSchema', 'forceScheme'),
                new MethodCallRename('Illuminate\Validation\Validator', 'addError', 'addFailure'),
                new MethodCallRename('Illuminate\Validation\Validator', 'doReplacements', 'makeReplacements'),
            ]),
        ]]);
};
