<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210514\Symfony\Component\HttpKernel\EventListener;

use RectorPrefix20210514\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use RectorPrefix20210514\Symfony\Component\HttpFoundation\RequestMatcherInterface;
use RectorPrefix20210514\Symfony\Component\HttpFoundation\RequestStack;
use RectorPrefix20210514\Symfony\Component\HttpKernel\Event\ExceptionEvent;
use RectorPrefix20210514\Symfony\Component\HttpKernel\Event\ResponseEvent;
use RectorPrefix20210514\Symfony\Component\HttpKernel\Event\TerminateEvent;
use RectorPrefix20210514\Symfony\Component\HttpKernel\KernelEvents;
use RectorPrefix20210514\Symfony\Component\HttpKernel\Profiler\Profiler;
/**
 * ProfilerListener collects data for the current request by listening to the kernel events.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @final
 */
class ProfilerListener implements \RectorPrefix20210514\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    protected $profiler;
    protected $matcher;
    protected $onlyException;
    protected $onlyMasterRequests;
    protected $exception;
    protected $profiles;
    protected $requestStack;
    protected $parents;
    /**
     * @param bool $onlyException      True if the profiler only collects data when an exception occurs, false otherwise
     * @param bool $onlyMasterRequests True if the profiler only collects data when the request is a master request, false otherwise
     */
    public function __construct(\RectorPrefix20210514\Symfony\Component\HttpKernel\Profiler\Profiler $profiler, \RectorPrefix20210514\Symfony\Component\HttpFoundation\RequestStack $requestStack, \RectorPrefix20210514\Symfony\Component\HttpFoundation\RequestMatcherInterface $matcher = null, bool $onlyException = \false, bool $onlyMasterRequests = \false)
    {
        $this->profiler = $profiler;
        $this->matcher = $matcher;
        $this->onlyException = $onlyException;
        $this->onlyMasterRequests = $onlyMasterRequests;
        $this->profiles = new \SplObjectStorage();
        $this->parents = new \SplObjectStorage();
        $this->requestStack = $requestStack;
    }
    /**
     * Handles the onKernelException event.
     */
    public function onKernelException(\RectorPrefix20210514\Symfony\Component\HttpKernel\Event\ExceptionEvent $event)
    {
        if ($this->onlyMasterRequests && !$event->isMasterRequest()) {
            return;
        }
        $this->exception = $event->getThrowable();
    }
    /**
     * Handles the onKernelResponse event.
     */
    public function onKernelResponse(\RectorPrefix20210514\Symfony\Component\HttpKernel\Event\ResponseEvent $event)
    {
        $master = $event->isMasterRequest();
        if ($this->onlyMasterRequests && !$master) {
            return;
        }
        if ($this->onlyException && null === $this->exception) {
            return;
        }
        $request = $event->getRequest();
        $exception = $this->exception;
        $this->exception = null;
        if (null !== $this->matcher && !$this->matcher->matches($request)) {
            return;
        }
        if (!($profile = $this->profiler->collect($request, $event->getResponse(), $exception))) {
            return;
        }
        $this->profiles[$request] = $profile;
        $this->parents[$request] = $this->requestStack->getParentRequest();
    }
    public function onKernelTerminate(\RectorPrefix20210514\Symfony\Component\HttpKernel\Event\TerminateEvent $event)
    {
        // attach children to parents
        foreach ($this->profiles as $request) {
            if (null !== ($parentRequest = $this->parents[$request])) {
                if (isset($this->profiles[$parentRequest])) {
                    $this->profiles[$parentRequest]->addChild($this->profiles[$request]);
                }
            }
        }
        // save profiles
        foreach ($this->profiles as $request) {
            $this->profiler->saveProfile($this->profiles[$request]);
        }
        $this->profiles = new \SplObjectStorage();
        $this->parents = new \SplObjectStorage();
    }
    public static function getSubscribedEvents() : array
    {
        return [\RectorPrefix20210514\Symfony\Component\HttpKernel\KernelEvents::RESPONSE => ['onKernelResponse', -100], \RectorPrefix20210514\Symfony\Component\HttpKernel\KernelEvents::EXCEPTION => ['onKernelException', 0], \RectorPrefix20210514\Symfony\Component\HttpKernel\KernelEvents::TERMINATE => ['onKernelTerminate', -1024]];
    }
}
