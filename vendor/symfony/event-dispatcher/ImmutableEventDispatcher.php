<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\EventDispatcher;

/**
 * A read-only proxy for an event dispatcher.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ImmutableEventDispatcher implements \RectorPrefix20211020\Symfony\Component\EventDispatcher\EventDispatcherInterface
{
    private $dispatcher;
    public function __construct(\RectorPrefix20211020\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    /**
     * {@inheritdoc}
     * @param object $event
     * @return object
     * @param string|null $eventName
     */
    public function dispatch($event, $eventName = null)
    {
        return $this->dispatcher->dispatch($event, $eventName);
    }
    /**
     * {@inheritdoc}
     * @param string $eventName
     * @param int $priority
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber
     */
    public function addSubscriber($subscriber)
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }
    /**
     * {@inheritdoc}
     * @param string $eventName
     */
    public function removeListener($eventName, $listener)
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber
     */
    public function removeSubscriber($subscriber)
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }
    /**
     * {@inheritdoc}
     * @param string|null $eventName
     */
    public function getListeners($eventName = null)
    {
        return $this->dispatcher->getListeners($eventName);
    }
    /**
     * {@inheritdoc}
     * @param string $eventName
     */
    public function getListenerPriority($eventName, $listener)
    {
        return $this->dispatcher->getListenerPriority($eventName, $listener);
    }
    /**
     * {@inheritdoc}
     * @param string|null $eventName
     */
    public function hasListeners($eventName = null)
    {
        return $this->dispatcher->hasListeners($eventName);
    }
}
