<?php

declare(strict_types=1);

namespace Rector\Laravel;

use Rector\Laravel\ValueObject\ArrayFunctionToMethodCall;
use Rector\Laravel\ValueObject\FunctionToMethodCall;

final class FunctionToServiceMap
{
    /**
     * @var FunctionToMethodCall[]
     */
    public $functionToMethodCalls = [];

    /**
     * @var ArrayFunctionToMethodCall[]
     */
    private $arrayFunctionToMethodCalls = [];

    public function __construct()
    {
        $this->initFunctionToMethodCalls();
        $this->initArrayFunctionToMethodCalls();
    }

    /**
     * @return FunctionToMethodCall|ArrayFunctionToMethodCall|null
     */
    public function findByFunction(string $functionName): ?object
    {
        foreach ($this->functionToMethodCalls as $functionToMethodCall) {
            if ($functionToMethodCall->getFunction() !== $functionName) {
                continue;
            }

            return $functionToMethodCall;
        }

        foreach ($this->arrayFunctionToMethodCalls as $arrayFunctionToMethodCall) {
            if ($arrayFunctionToMethodCall->getFunction() !== $functionName) {
                continue;
            }

            return $arrayFunctionToMethodCall;
        }

        return null;
    }

    private function initArrayFunctionToMethodCalls(): void
    {
        $this->arrayFunctionToMethodCalls[] = new ArrayFunctionToMethodCall(
            'config',
            'Illuminate\Contracts\Config\Repository',
            'configRepository',
            'set',
            'get'
        );

        $this->arrayFunctionToMethodCalls[] = new ArrayFunctionToMethodCall(
            'session',
            'Illuminate\Session\SessionManager',
            'sessionManager',
            'put',
            'get'
        );
    }

    private function initFunctionToMethodCalls(): void
    {
        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'auth', 'Illuminate\Contracts\Auth\Guard', 'guard'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'policy', 'Illuminate\Contracts\Auth\Access\Gate', 'policy', 'getPolicyFor'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'cookie', 'Illuminate\Contracts\Cookie\Factory', 'cookieFactory', 'make'
        );

        // router
        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'put', 'Illuminate\Routing\Router', 'router', 'put'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'get', 'Illuminate\Routing\Router', 'router', 'get'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'post', 'Illuminate\Routing\Router', 'router', 'post'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'patch', 'Illuminate\Routing\Router', 'router', 'patch'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'delete', 'Illuminate\Routing\Router', 'router', 'delete'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'resource', 'Illuminate\Routing\Router', 'router', 'resource'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'response', 'Illuminate\Contracts\Routing\ResponseFactory', 'responseFactory', 'make'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'info', 'Illuminate\Log\Writer', 'logWriter', 'info'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'view', 'Illuminate\Contracts\View\Factory', 'viewFactory', 'make'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'bcrypt', 'Illuminate\Hashing\BcryptHasher', 'bcryptHasher', 'make'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'redirect', 'Illuminate\Routing\Redirector', 'redirector', 'back'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'broadcast', 'Illuminate\Contracts\Broadcasting\Factory', 'broadcastFactory', 'event'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'event', 'Illuminate\Events\Dispatcher', 'eventDispatcher', 'fire'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'dispatch', 'Illuminate\Events\Dispatcher', 'eventDispatcher', 'dispatch'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'route', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'route'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'asset', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'asset'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'url', 'Illuminate\Contracts\Routing\UrlGenerator', 'urlGenerator', 'to'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'action', 'Illuminate\Routing\UrlGenerator', 'urlGenerator', 'action'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'trans', 'Illuminate\Translation\Translator', 'translator', 'trans'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'trans_choice', 'Illuminate\Translation\Translator', 'translator', 'transChoice'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'logger', 'Illuminate\Log\Writer', 'logWriter', 'debug'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'back', 'Illuminate\Routing\Redirector', 'redirector', 'back', 'back'
        );
    }
}
