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

use RectorPrefix20211020\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Cookie;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Session;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\SessionInterface;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\RequestEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\ResponseEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents;
/**
 * TestSessionListener.
 *
 * Saves session in test environment.
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 */
abstract class AbstractTestSessionListener implements \RectorPrefix20211020\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    private $sessionId;
    private $sessionOptions;
    public function __construct(array $sessionOptions = [])
    {
        $this->sessionOptions = $sessionOptions;
    }
    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onKernelRequest($event)
    {
        if (!$event->isMainRequest()) {
            return;
        }
        // bootstrap the session
        if (!($session = $this->getSession())) {
            return;
        }
        $cookies = $event->getRequest()->cookies;
        if ($cookies->has($session->getName())) {
            $this->sessionId = $cookies->get($session->getName());
            $session->setId($this->sessionId);
        }
    }
    /**
     * Checks if session was initialized and saves if current request is the main request
     * Runs on 'kernel.response' in test environment.
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse($event)
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }
        $session = $request->getSession();
        if ($wasStarted = $session->isStarted()) {
            $session->save();
        }
        if ($session instanceof \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Session ? !$session->isEmpty() || null !== $this->sessionId && $session->getId() !== $this->sessionId : $wasStarted) {
            $params = \session_get_cookie_params() + ['samesite' => null];
            foreach ($this->sessionOptions as $k => $v) {
                if (\strncmp($k, 'cookie_', \strlen('cookie_')) === 0) {
                    $params[\substr($k, 7)] = $v;
                }
            }
            foreach ($event->getResponse()->headers->getCookies() as $cookie) {
                if ($session->getName() === $cookie->getName() && $params['path'] === $cookie->getPath() && $params['domain'] == $cookie->getDomain()) {
                    return;
                }
            }
            $event->getResponse()->headers->setCookie(new \RectorPrefix20211020\Symfony\Component\HttpFoundation\Cookie($session->getName(), $session->getId(), 0 === $params['lifetime'] ? 0 : \time() + $params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly'], \false, $params['samesite'] ?: null));
            $this->sessionId = $session->getId();
        }
    }
    public static function getSubscribedEvents() : array
    {
        return [\RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::REQUEST => ['onKernelRequest', 192], \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::RESPONSE => ['onKernelResponse', -128]];
    }
    /**
     * Gets the session object.
     *
     * @return SessionInterface|null A SessionInterface instance or null if no session is available
     */
    protected abstract function getSession();
}
