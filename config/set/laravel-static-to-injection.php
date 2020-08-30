<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\FuncCallToNewRector;
use Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;
use Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\ValueObject\ArrayFuncCallToMethodCall;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');

    $services = $containerConfigurator->services();

    $services->set(FacadeStaticCallToConstructorInjectionRector::class);

    $services->set(RequestStaticValidateToInjectRector::class);

    // @see https://github.com/laravel/framework/blob/78828bc779e410e03cc6465f002b834eadf160d2/src/Illuminate/Foundation/helpers.php#L959
    // @see https://gist.github.com/barryvdh/bb6ffc5d11e0a75dba67
    $services->set(FuncCallToMethodCallRector::class)
        ->call('configure', [[
            FuncCallToMethodCallRector::FUNCTIONS_TO_METHOD_CALLS => inline_value_objects([
                new FuncCallToMethodCall('auth', 'Illuminate\Contracts\Auth\Guard', 'guard'),
                new FuncCallToMethodCall('policy', 'Illuminate\Contracts\Auth\Access\Gate', 'policy', 'getPolicyFor'),
                new FuncCallToMethodCall('cookie', 'Illuminate\Contracts\Cookie\Factory', 'cookieFactory', 'make'),
                // router
                new FuncCallToMethodCall('put', 'Illuminate\Routing\Router', 'router', 'put'),
                new FuncCallToMethodCall('get', 'Illuminate\Routing\Router', 'router', 'get'),
                new FuncCallToMethodCall('post', 'Illuminate\Routing\Router', 'router', 'post'),
                new FuncCallToMethodCall('patch', 'Illuminate\Routing\Router', 'router', 'patch'),
                new FuncCallToMethodCall('delete', 'Illuminate\Routing\Router', 'router', 'delete'),
                new FuncCallToMethodCall('resource', 'Illuminate\Routing\Router', 'router', 'resource'),
                new FuncCallToMethodCall(
                    'response',
                    'Illuminate\Contracts\Routing\ResponseFactory',
                    'responseFactory',
                    'make'
                ),
                new FuncCallToMethodCall('info', 'Illuminate\Log\Writer', 'logWriter', 'info'),
                new FuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'viewFactory', 'make'),
                new FuncCallToMethodCall('bcrypt', 'Illuminate\Hashing\BcryptHasher', 'bcryptHasher', 'make'),
                new FuncCallToMethodCall('redirect', 'Illuminate\Routing\Redirector', 'redirector', 'back'),
                new FuncCallToMethodCall(
                    'broadcast',
                    'Illuminate\Contracts\Broadcasting\Factory',
                    'broadcastFactory',
                    'event'
                ),
                new FuncCallToMethodCall('event', 'Illuminate\Events\Dispatcher', 'eventDispatcher', 'fire'),
                new FuncCallToMethodCall('dispatch', 'Illuminate\Events\Dispatcher', 'eventDispatcher', 'dispatch'),
                new FuncCallToMethodCall('route', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'route'),
                new FuncCallToMethodCall('asset', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'asset'),
                new FuncCallToMethodCall('url', 'Illuminate\Contracts\Routing\UrlGenerator', 'urlGenerator', 'to'),
                new FuncCallToMethodCall('action', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'action'),
                new FuncCallToMethodCall('trans', 'Illuminate\Translation\Translator', 'translator', 'trans'),
                new FuncCallToMethodCall(
                    'trans_choice',
                    'Illuminate\Translation\Translator',
                    'translator',
                    'transChoice'
                ),
                new FuncCallToMethodCall('logger', 'Illuminate\Log\Writer', 'logWriter', 'debug'),
                new FuncCallToMethodCall('back', 'Illuminate\Routing\Redirector', 'redirector', 'back', 'back'),
            ]),
            FuncCallToMethodCallRector::ARRAY_FUNCTIONS_TO_METHOD_CALLS => inline_value_objects([
                new ArrayFuncCallToMethodCall(
                    'config',
                    'Illuminate\Contracts\Config\Repository',
                    'configRepository',
                    'set',
                    'get'
                ),
                new ArrayFuncCallToMethodCall(
                    'session',
                    'Illuminate\Session\SessionManager',
                    'sessionManager',
                    'put',
                    'get'
              ),
            ]),
        ]]);

    $services->set(FuncCallToNewRector::class)
        ->call('configure', [[
            FuncCallToNewRector::FUNCTION_TO_NEW => [
                'collect' => 'Illuminate\Support\Collection',
            ],
        ]]);
};
