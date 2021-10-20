<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel;

use RectorPrefix20211020\Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\RequestStack;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Response;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ControllerEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ExceptionEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\RequestEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ResponseEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\TerminateEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ViewEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\ControllerDoesNotReturnResponseException;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use RectorPrefix20211020\Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
// Help opcache.preload discover always-needed symbols
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent::class);
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ControllerEvent::class);
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ExceptionEvent::class);
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpKernel\Event\FinishRequestEvent::class);
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpKernel\Event\RequestEvent::class);
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ResponseEvent::class);
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpKernel\Event\TerminateEvent::class);
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ViewEvent::class);
\class_exists(\RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::class);
/**
 * HttpKernel notifies events to convert a Request object to a Response one.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HttpKernel implements \RectorPrefix20211020\Symfony\Component\HttpKernel\HttpKernelInterface, \RectorPrefix20211020\Symfony\Component\HttpKernel\TerminableInterface
{
    protected $dispatcher;
    protected $resolver;
    protected $requestStack;
    private $argumentResolver;
    public function __construct(\RectorPrefix20211020\Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher, \RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $resolver, \RectorPrefix20211020\Symfony\Component\HttpFoundation\RequestStack $requestStack = null, \RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface $argumentResolver = null)
    {
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver;
        $this->requestStack = $requestStack ?? new \RectorPrefix20211020\Symfony\Component\HttpFoundation\RequestStack();
        $this->argumentResolver = $argumentResolver;
        if (null === $this->argumentResolver) {
            $this->argumentResolver = new \RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ArgumentResolver();
        }
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $type
     * @param bool $catch
     */
    public function handle($request, $type = \RectorPrefix20211020\Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST, $catch = \true)
    {
        $request->headers->set('X-Php-Ob-Level', (string) \ob_get_level());
        try {
            return $this->handleRaw($request, $type);
        } catch (\Exception $e) {
            if ($e instanceof \RectorPrefix20211020\Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface) {
                $e = new \RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\BadRequestHttpException($e->getMessage(), $e);
            }
            if (\false === $catch) {
                $this->finishRequest($request, $type);
                throw $e;
            }
            return $this->handleThrowable($e, $request, $type);
        }
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function terminate($request, $response)
    {
        $this->dispatcher->dispatch(new \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\TerminateEvent($this, $request, $response), \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::TERMINATE);
    }
    /**
     * @internal
     * @param \Throwable $exception
     * @param \Symfony\Component\HttpFoundation\Request|null $request
     */
    public function terminateWithException($exception, $request = null)
    {
        if (!($request = $request ?: $this->requestStack->getMainRequest())) {
            throw $exception;
        }
        $response = $this->handleThrowable($exception, $request, self::MAIN_REQUEST);
        $response->sendHeaders();
        $response->sendContent();
        $this->terminate($request, $response);
    }
    /**
     * Handles a request to convert it to a response.
     *
     * Exceptions are not caught.
     *
     * @throws \LogicException       If one of the listener does not behave as expected
     * @throws NotFoundHttpException When controller cannot be found
     */
    private function handleRaw(\RectorPrefix20211020\Symfony\Component\HttpFoundation\Request $request, int $type = self::MAIN_REQUEST) : \RectorPrefix20211020\Symfony\Component\HttpFoundation\Response
    {
        $this->requestStack->push($request);
        // request
        $event = new \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\RequestEvent($this, $request, $type);
        $this->dispatcher->dispatch($event, \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::REQUEST);
        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $request, $type);
        }
        // load controller
        if (\false === ($controller = $this->resolver->getController($request))) {
            throw new \RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\NotFoundHttpException(\sprintf('Unable to find the controller for path "%s". The route is wrongly configured.', $request->getPathInfo()));
        }
        $event = new \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ControllerEvent($this, $controller, $request, $type);
        $this->dispatcher->dispatch($event, \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::CONTROLLER);
        $controller = $event->getController();
        // controller arguments
        $arguments = $this->argumentResolver->getArguments($request, $controller);
        $event = new \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent($this, $controller, $arguments, $request, $type);
        $this->dispatcher->dispatch($event, \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::CONTROLLER_ARGUMENTS);
        $controller = $event->getController();
        $arguments = $event->getArguments();
        // call controller
        $response = $controller(...$arguments);
        // view
        if (!$response instanceof \RectorPrefix20211020\Symfony\Component\HttpFoundation\Response) {
            $event = new \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ViewEvent($this, $request, $type, $response);
            $this->dispatcher->dispatch($event, \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::VIEW);
            if ($event->hasResponse()) {
                $response = $event->getResponse();
            } else {
                $msg = \sprintf('The controller must return a "Symfony\\Component\\HttpFoundation\\Response" object but it returned %s.', $this->varToString($response));
                // the user may have forgotten to return something
                if (null === $response) {
                    $msg .= ' Did you forget to add a return statement somewhere in your controller?';
                }
                throw new \RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\ControllerDoesNotReturnResponseException($msg, $controller, __FILE__, __LINE__ - 17);
            }
        }
        return $this->filterResponse($response, $request, $type);
    }
    /**
     * Filters a response object.
     *
     * @throws \RuntimeException if the passed object is not a Response instance
     */
    private function filterResponse(\RectorPrefix20211020\Symfony\Component\HttpFoundation\Response $response, \RectorPrefix20211020\Symfony\Component\HttpFoundation\Request $request, int $type) : \RectorPrefix20211020\Symfony\Component\HttpFoundation\Response
    {
        $event = new \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ResponseEvent($this, $request, $type, $response);
        $this->dispatcher->dispatch($event, \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::RESPONSE);
        $this->finishRequest($request, $type);
        return $event->getResponse();
    }
    /**
     * Publishes the finish request event, then pop the request from the stack.
     *
     * Note that the order of the operations is important here, otherwise
     * operations such as {@link RequestStack::getParentRequest()} can lead to
     * weird results.
     */
    private function finishRequest(\RectorPrefix20211020\Symfony\Component\HttpFoundation\Request $request, int $type)
    {
        $this->dispatcher->dispatch(new \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\FinishRequestEvent($this, $request, $type), \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::FINISH_REQUEST);
        $this->requestStack->pop();
    }
    /**
     * Handles a throwable by trying to convert it to a Response.
     *
     * @throws \Exception
     */
    private function handleThrowable(\Throwable $e, \RectorPrefix20211020\Symfony\Component\HttpFoundation\Request $request, int $type) : \RectorPrefix20211020\Symfony\Component\HttpFoundation\Response
    {
        $event = new \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ExceptionEvent($this, $request, $type, $e);
        $this->dispatcher->dispatch($event, \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::EXCEPTION);
        // a listener might have replaced the exception
        $e = $event->getThrowable();
        if (!$event->hasResponse()) {
            $this->finishRequest($request, $type);
            throw $e;
        }
        $response = $event->getResponse();
        // the developer asked for a specific status code
        if (!$event->isAllowingCustomResponseCode() && !$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
            // ensure that we actually have an error response
            if ($e instanceof \RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                // keep the HTTP status code and headers
                $response->setStatusCode($e->getStatusCode());
                $response->headers->add($e->getHeaders());
            } else {
                $response->setStatusCode(500);
            }
        }
        try {
            return $this->filterResponse($response, $request, $type);
        } catch (\Exception $e) {
            return $response;
        }
    }
    /**
     * Returns a human-readable string for the specified variable.
     */
    private function varToString($var) : string
    {
        if (\is_object($var)) {
            return \sprintf('an object of type %s', \get_class($var));
        }
        if (\is_array($var)) {
            $a = [];
            foreach ($var as $k => $v) {
                $a[] = \sprintf('%s => ...', $k);
            }
            return \sprintf('an array ([%s])', \mb_substr(\implode(', ', $a), 0, 255));
        }
        if (\is_resource($var)) {
            return \sprintf('a resource (%s)', \get_resource_type($var));
        }
        if (null === $var) {
            return 'null';
        }
        if (\false === $var) {
            return 'a boolean value (false)';
        }
        if (\true === $var) {
            return 'a boolean value (true)';
        }
        if (\is_string($var)) {
            return \sprintf('a string ("%s%s")', \mb_substr($var, 0, 255), \mb_strlen($var) > 255 ? '...' : '');
        }
        if (\is_numeric($var)) {
            return \sprintf('a number (%s)', (string) $var);
        }
        return (string) $var;
    }
}
