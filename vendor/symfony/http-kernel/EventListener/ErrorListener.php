<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\EventListener;

use RectorPrefix20211020\Psr\Log\LoggerInterface;
use RectorPrefix20211020\Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;
use RectorPrefix20211020\Symfony\Component\ErrorHandler\Exception\FlattenException;
use RectorPrefix20211020\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ExceptionEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ResponseEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use RectorPrefix20211020\Symfony\Component\HttpKernel\HttpKernelInterface;
use RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ErrorListener implements \RectorPrefix20211020\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    protected $controller;
    protected $logger;
    protected $debug;
    public function __construct($controller, \RectorPrefix20211020\Psr\Log\LoggerInterface $logger = null, bool $debug = \false)
    {
        $this->controller = $controller;
        $this->logger = $logger;
        $this->debug = $debug;
    }
    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function logKernelException($event)
    {
        $e = \RectorPrefix20211020\Symfony\Component\ErrorHandler\Exception\FlattenException::createFromThrowable($event->getThrowable());
        $this->logException($event->getThrowable(), \sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', $e->getClass(), $e->getMessage(), $e->getFile(), $e->getLine()));
    }
    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException($event)
    {
        if (null === $this->controller) {
            return;
        }
        $exception = $event->getThrowable();
        $request = $this->duplicateRequest($exception, $event->getRequest());
        try {
            $response = $event->getKernel()->handle($request, \RectorPrefix20211020\Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST, \false);
        } catch (\Exception $e) {
            $f = \RectorPrefix20211020\Symfony\Component\ErrorHandler\Exception\FlattenException::createFromThrowable($e);
            $this->logException($e, \sprintf('Exception thrown when handling an exception (%s: %s at %s line %s)', $f->getClass(), $f->getMessage(), $e->getFile(), $e->getLine()));
            $prev = $e;
            do {
                if ($exception === ($wrapper = $prev)) {
                    throw $e;
                }
            } while ($prev = $wrapper->getPrevious());
            $prev = new \ReflectionProperty($wrapper instanceof \Exception ? \Exception::class : \Error::class, 'previous');
            $prev->setAccessible(\true);
            $prev->setValue($wrapper, $exception);
            throw $e;
        }
        $event->setResponse($response);
        if ($this->debug) {
            $event->getRequest()->attributes->set('_remove_csp_headers', \true);
        }
    }
    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function removeCspHeader($event) : void
    {
        if ($this->debug && $event->getRequest()->attributes->get('_remove_csp_headers', \false)) {
            $event->getResponse()->headers->remove('Content-Security-Policy');
        }
    }
    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent $event
     */
    public function onControllerArguments($event)
    {
        $e = $event->getRequest()->attributes->get('exception');
        if (!$e instanceof \Throwable || \false === ($k = \array_search($e, $event->getArguments(), \true))) {
            return;
        }
        $r = new \ReflectionFunction(\Closure::fromCallable($event->getController()));
        $r = $r->getParameters()[$k] ?? null;
        if ($r && (!($r = $r->getType()) instanceof \ReflectionNamedType || \in_array($r->getName(), [\RectorPrefix20211020\Symfony\Component\ErrorHandler\Exception\FlattenException::class, \RectorPrefix20211020\Symfony\Component\Debug\Exception\FlattenException::class], \true))) {
            $arguments = $event->getArguments();
            $arguments[$k] = \RectorPrefix20211020\Symfony\Component\ErrorHandler\Exception\FlattenException::createFromThrowable($e);
            $event->setArguments($arguments);
        }
    }
    public static function getSubscribedEvents() : array
    {
        return [\RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::CONTROLLER_ARGUMENTS => 'onControllerArguments', \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::EXCEPTION => [['logKernelException', 0], ['onKernelException', -128]], \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::RESPONSE => ['removeCspHeader', -128]];
    }
    /**
     * Logs an exception.
     * @param \Throwable $exception
     * @param string $message
     */
    protected function logException($exception, $message) : void
    {
        if (null !== $this->logger) {
            if (!$exception instanceof \RectorPrefix20211020\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->critical($message, ['exception' => $exception]);
            } else {
                $this->logger->error($message, ['exception' => $exception]);
            }
        }
    }
    /**
     * Clones the request for the exception.
     * @param \Throwable $exception
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function duplicateRequest($exception, $request) : \RectorPrefix20211020\Symfony\Component\HttpFoundation\Request
    {
        $attributes = ['_controller' => $this->controller, 'exception' => $exception, 'logger' => $this->logger instanceof \RectorPrefix20211020\Symfony\Component\HttpKernel\Log\DebugLoggerInterface ? $this->logger : null];
        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');
        return $request;
    }
}
