<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\FuncCallToNewRector;
use Rector\Laravel\Rector\FuncCall\HelperFunctionToConstructorInjectionRector;
use Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;
use Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\ValueObject\ArrayFunctionToMethodCall;
use Rector\Transform\ValueObject\FunctionToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');

    $services = $containerConfigurator->services();

    $services->set(FacadeStaticCallToConstructorInjectionRector::class);

    $services->set(RequestStaticValidateToInjectRector::class);

    $services->set(HelperFunctionToConstructorInjectionRector::class)
        ->call('configure', [[
            HelperFunctionToConstructorInjectionRector::FUNCTIONS_TO_METHOD_CALLS => inline_value_objects([
                new FunctionToMethodCall('auth', 'Illuminate\Contracts\Auth\Guard', 'guard'),
                new FunctionToMethodCall('policy', 'Illuminate\Contracts\Auth\Access\Gate', 'policy', 'getPolicyFor'),
                new FunctionToMethodCall('cookie', 'Illuminate\Contracts\Cookie\Factory', 'cookieFactory', 'make'),
                // router
                new FunctionToMethodCall('put', 'Illuminate\Routing\Router', 'router', 'put'),
                new FunctionToMethodCall('get', 'Illuminate\Routing\Router', 'router', 'get'),
                new FunctionToMethodCall('post', 'Illuminate\Routing\Router', 'router', 'post'),
                new FunctionToMethodCall('patch', 'Illuminate\Routing\Router', 'router', 'patch'),
                new FunctionToMethodCall('delete', 'Illuminate\Routing\Router', 'router', 'delete'),
                new FunctionToMethodCall('resource', 'Illuminate\Routing\Router', 'router', 'resource'),
                new FunctionToMethodCall(
                    'response',
                    'Illuminate\Contracts\Routing\ResponseFactory',
                    'responseFactory',
                    'make'
                ),
                new FunctionToMethodCall('info', 'Illuminate\Log\Writer', 'logWriter', 'info'),
                new FunctionToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'viewFactory', 'make'),
                new FunctionToMethodCall('bcrypt', 'Illuminate\Hashing\BcryptHasher', 'bcryptHasher', 'make'),
                new FunctionToMethodCall('redirect', 'Illuminate\Routing\Redirector', 'redirector', 'back'),
                new FunctionToMethodCall(
                    'broadcast',
                    'Illuminate\Contracts\Broadcasting\Factory',
                    'broadcastFactory',
                    'event'
                ),
                new FunctionToMethodCall('event', 'Illuminate\Events\Dispatcher', 'eventDispatcher', 'fire'),
                new FunctionToMethodCall('dispatch', 'Illuminate\Events\Dispatcher', 'eventDispatcher', 'dispatch'),
                new FunctionToMethodCall('route', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'route'),
                new FunctionToMethodCall('asset', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'asset'),
                new FunctionToMethodCall('url', 'Illuminate\Contracts\Routing\UrlGenerator', 'urlGenerator', 'to'),
                new FunctionToMethodCall('action', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'action'),
                new FunctionToMethodCall('trans', 'Illuminate\Translation\Translator', 'translator', 'trans'),
                new FunctionToMethodCall(
                    'trans_choice',
                    'Illuminate\Translation\Translator',
                    'translator',
                    'transChoice'
                ),
                new FunctionToMethodCall('logger', 'Illuminate\Log\Writer', 'logWriter', 'debug'),
                new FunctionToMethodCall('back', 'Illuminate\Routing\Redirector', 'redirector', 'back', 'back'),
            ]),
            HelperFunctionToConstructorInjectionRector::ARRAY_FUNCTIONS_TO_METHOD_CALLS => inline_value_objects([
                new ArrayFunctionToMethodCall(
                    'config',
                    'Illuminate\Contracts\Config\Repository',
                    'configRepository',
                    'set',
                    'get'
                ),
                new ArrayFunctionToMethodCall(
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
