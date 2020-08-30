<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\FuncCallToNewRector;
use Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;
use Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Rector\Transform\ValueObject\ArrayFuncCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');

    $services = $containerConfigurator->services();
    $services->set(FacadeStaticCallToConstructorInjectionRector::class);
    $services->set(RequestStaticValidateToInjectRector::class);

    // @see https://github.com/laravel/framework/blob/78828bc779e410e03cc6465f002b834eadf160d2/src/Illuminate/Foundation/helpers.php#L959
    // @see https://gist.github.com/barryvdh/bb6ffc5d11e0a75dba67
    $services->set(ArgumentFuncCallToMethodCallRector::class)
        ->call('configure', [[
            ArgumentFuncCallToMethodCallRector::FUNCTIONS_TO_METHOD_CALLS => inline_value_objects([
                new ArgumentFuncCallToMethodCall('auth', 'Illuminate\Contracts\Auth\Guard'),
                new ArgumentFuncCallToMethodCall('policy', 'Illuminate\Contracts\Auth\Access\Gate', 'getPolicyFor'),
                new ArgumentFuncCallToMethodCall('cookie', 'Illuminate\Contracts\Cookie\Factory', 'make'),
                // router
                new ArgumentFuncCallToMethodCall('put', 'Illuminate\Routing\Router', 'put'),
                new ArgumentFuncCallToMethodCall('get', 'Illuminate\Routing\Router', 'get'),
                new ArgumentFuncCallToMethodCall('post', 'Illuminate\Routing\Router', 'post'),
                new ArgumentFuncCallToMethodCall('patch', 'Illuminate\Routing\Router', 'patch'),
                new ArgumentFuncCallToMethodCall('delete', 'Illuminate\Routing\Router', 'delete'),
                new ArgumentFuncCallToMethodCall('resource', 'Illuminate\Routing\Router', 'resource'),
                new ArgumentFuncCallToMethodCall(
                    'response',
                    'Illuminate\Contracts\Routing\ResponseFactory',
                    'make'
                ),
                new ArgumentFuncCallToMethodCall('info', 'Illuminate\Log\Writer', 'info'),
                new ArgumentFuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'make'),
                new ArgumentFuncCallToMethodCall('bcrypt', 'Illuminate\Hashing\BcryptHasher', 'make'),
                new ArgumentFuncCallToMethodCall('redirect', 'Illuminate\Routing\Redirector', 'back'),
                new ArgumentFuncCallToMethodCall('broadcast', 'Illuminate\Contracts\Broadcasting\Factory', 'event'),
                new ArgumentFuncCallToMethodCall('event', 'Illuminate\Events\Dispatcher', 'fire'),
                new ArgumentFuncCallToMethodCall('dispatch', 'Illuminate\Events\Dispatcher', 'dispatch'),
                new ArgumentFuncCallToMethodCall('route', 'Illuminate\Routing\UrlGenerator', 'route'),
                new ArgumentFuncCallToMethodCall('asset', 'Illuminate\Routing\UrlGenerator', 'asset'),
                new ArgumentFuncCallToMethodCall('url', 'Illuminate\Contracts\Routing\UrlGenerator', 'to'),
                new ArgumentFuncCallToMethodCall('action', 'Illuminate\Routing\UrlGenerator', 'action'),
                new ArgumentFuncCallToMethodCall('trans', 'Illuminate\Translation\Translator', 'trans'),
                new ArgumentFuncCallToMethodCall(
                    'trans_choice',
                    'Illuminate\Translation\Translator',
                    'transChoice'
                ),
                new ArgumentFuncCallToMethodCall('logger', 'Illuminate\Log\Writer', 'debug'),
                new ArgumentFuncCallToMethodCall('back', 'Illuminate\Routing\Redirector', 'back', 'back'),
            ]),
            ArgumentFuncCallToMethodCallRector::ARRAY_FUNCTIONS_TO_METHOD_CALLS => inline_value_objects([
                new ArrayFuncCallToMethodCall('config', 'Illuminate\Contracts\Config\Repository', 'set', 'get'),
                new ArrayFuncCallToMethodCall('session', 'Illuminate\Session\SessionManager', 'put', 'get'),
            ]),
        ]]);

    $services->set(FuncCallToNewRector::class)
        ->call('configure', [[
            FuncCallToNewRector::FUNCTION_TO_NEW => [
                'collect' => 'Illuminate\Support\Collection',
            ],
        ]]);
};
