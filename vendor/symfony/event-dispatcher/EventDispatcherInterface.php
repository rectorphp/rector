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

use RectorPrefix20211020\Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;
/**
 * The EventDispatcherInterface is the central point of Symfony's event listener system.
 * Listeners are registered on the manager and events are dispatched through the
 * manager.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface EventDispatcherInterface extends \RectorPrefix20211020\Symfony\Contracts\EventDispatcher\EventDispatcherInterface
{
    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param int $priority The higher this value, the earlier an event
     *                      listener will be triggered in the chain (defaults to 0)
     * @param string $eventName
     * @param callable $listener
     */
    public function addListener($eventName, $listener, $priority = 0);
    /**
     * Adds an event subscriber.
     *
     * The subscriber is asked for all the events it is
     * interested in and added as a listener for these events.
     * @param \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber
     */
    public function addSubscriber($subscriber);
    /**
     * Removes an event listener from the specified events.
     * @param string $eventName
     * @param callable $listener
     */
    public function removeListener($eventName, $listener);
    /**
     * @param \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber
     */
    public function removeSubscriber($subscriber);
    /**
     * Gets the listeners of a specific event or all listeners sorted by descending priority.
     *
     * @return array The event listeners for the specified event, or all event listeners by event name
     * @param string|null $eventName
     */
    public function getListeners($eventName = null);
    /**
     * Gets the listener priority for a specific event.
     *
     * Returns null if the event or the listener does not exist.
     *
     * @return int|null The event listener priority
     * @param string $eventName
     * @param callable $listener
     */
    public function getListenerPriority($eventName, $listener);
    /**
     * Checks whether an event has any registered listeners.
     *
     * @return bool true if the specified event has any listeners, false otherwise
     * @param string|null $eventName
     */
    public function hasListeners($eventName = null);
}
