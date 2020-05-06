<?php

declare(strict_types=1);

namespace Rector\Laravel;

use Rector\Laravel\ValueObject\ArrayFunctionToMethodCall;
use Rector\Laravel\ValueObject\FunctionToMethodCall;

final class FunctionToServiceMap
{
    /**
     * @var string
     */
    private const MAKE = 'make';

    /**
     * @var string
     */
    private const PUT = 'put';

    /**
     * @var string
     */
    private const ROUTER = 'router';

    /**
     * @var string
     */
    private const GET = 'get';

    /**
     * @var string
     */
    private const BACK = 'back';

    /**
     * @var string
     */
    private const URL_GENERATOR = 'urlGenerator';

    /**
     * @var FunctionToMethodCall[]
     */
    private $functionToMethodCalls = [];

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

    private function initFunctionToMethodCalls(): void
    {
        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'auth', 'Illuminate\Contracts\Auth\Guard', 'guard'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'policy', 'Illuminate\Contracts\Auth\Access\Gate', 'policy', 'getPolicyFor'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'cookie', 'Illuminate\Contracts\Cookie\Factory', 'cookieFactory', self::MAKE
        );

        // router
        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            self::PUT, 'Illuminate\Routing\Router', self::ROUTER, self::PUT
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            self::GET, 'Illuminate\Routing\Router', self::ROUTER, self::GET
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'post', 'Illuminate\Routing\Router', self::ROUTER, 'post'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'patch', 'Illuminate\Routing\Router', self::ROUTER, 'patch'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'delete', 'Illuminate\Routing\Router', self::ROUTER, 'delete'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'resource', 'Illuminate\Routing\Router', self::ROUTER, 'resource'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'response', 'Illuminate\Contracts\Routing\ResponseFactory', 'responseFactory', self::MAKE
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'info', 'Illuminate\Log\Writer', 'logWriter', 'info'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'view', 'Illuminate\Contracts\View\Factory', 'viewFactory', self::MAKE
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'bcrypt', 'Illuminate\Hashing\BcryptHasher', 'bcryptHasher', self::MAKE
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'redirect', 'Illuminate\Routing\Redirector', 'redirector', self::BACK
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
            'route', 'Illuminate\Routing\UrlGenerator', self::URL_GENERATOR, 'route'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'asset', 'Illuminate\Routing\UrlGenerator', self::URL_GENERATOR, 'asset'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'url', 'Illuminate\Contracts\Routing\UrlGenerator', self::URL_GENERATOR, 'to'
        );

        $this->functionToMethodCalls[] = new FunctionToMethodCall(
            'action', 'Illuminate\Routing\UrlGenerator', self::URL_GENERATOR, 'action'
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
            self::BACK, 'Illuminate\Routing\Redirector', 'redirector', self::BACK, self::BACK
        );
    }

    private function initArrayFunctionToMethodCalls(): void
    {
        $this->arrayFunctionToMethodCalls[] = new ArrayFunctionToMethodCall(
            'config',
            'Illuminate\Contracts\Config\Repository',
            'configRepository',
            'set',
            self::GET
        );

        $this->arrayFunctionToMethodCalls[] = new ArrayFunctionToMethodCall(
            'session',
            'Illuminate\Session\SessionManager',
            'sessionManager',
            self::PUT,
            self::GET
        );
    }
}
