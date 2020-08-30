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
                new FuncCallToMethodCall('auth', 'Illuminate\Contracts\Auth\Guard'),
                new FuncCallToMethodCall('policy', 'Illuminate\Contracts\Auth\Access\Gate', 'getPolicyFor'),
                new FuncCallToMethodCall('cookie', 'Illuminate\Contracts\Cookie\Factory', 'make'),
                // router
                new FuncCallToMethodCall('put', 'Illuminate\Routing\Router', 'put'),
                new FuncCallToMethodCall('get', 'Illuminate\Routing\Router', 'get'),
                new FuncCallToMethodCall('post', 'Illuminate\Routing\Router', 'post'),
                new FuncCallToMethodCall('patch', 'Illuminate\Routing\Router', 'patch'),
                new FuncCallToMethodCall('delete', 'Illuminate\Routing\Router', 'delete'),
                new FuncCallToMethodCall('resource', 'Illuminate\Routing\Router', 'resource'),
                new FuncCallToMethodCall('response', 'Illuminate\Contracts\Routing\ResponseFactory', 'make'),
                new FuncCallToMethodCall('info', 'Illuminate\Log\Writer', 'info'),
                new FuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'make'),
                new FuncCallToMethodCall('bcrypt', 'Illuminate\Hashing\BcryptHasher', 'make'),
                new FuncCallToMethodCall('redirect', 'Illuminate\Routing\Redirector', 'back'),
                new FuncCallToMethodCall('broadcast', 'Illuminate\Contracts\Broadcasting\Factory', 'event'),
                new FuncCallToMethodCall('event', 'Illuminate\Events\Dispatcher', 'fire'),
                new FuncCallToMethodCall('dispatch', 'Illuminate\Events\Dispatcher', 'dispatch'),
                new FuncCallToMethodCall('route', 'Illuminate\Routing\UrlGenerator', 'route'),
                new FuncCallToMethodCall('asset', 'Illuminate\Routing\UrlGenerator', 'asset'),
                new FuncCallToMethodCall('url', 'Illuminate\Contracts\Routing\UrlGenerator', 'to'),
                new FuncCallToMethodCall('action', 'Illuminate\Routing\UrlGenerator', 'action'),
                new FuncCallToMethodCall('trans', 'Illuminate\Translation\Translator', 'trans'),
                new FuncCallToMethodCall('trans_choice', 'Illuminate\Translation\Translator', 'transChoice'),
                new FuncCallToMethodCall('logger', 'Illuminate\Log\Writer', 'debug'),
                new FuncCallToMethodCall('back', 'Illuminate\Routing\Redirector', 'back', 'back'),
            ]),
            FuncCallToMethodCallRector::ARRAY_FUNCTIONS_TO_METHOD_CALLS => inline_value_objects([
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
