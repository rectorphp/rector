<?php

declare (strict_types=1);
/*
 * This file is part of Evenement.
 *
 * (c) Igor Wiedler <igor@wiedler.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211123\Evenement;

use InvalidArgumentException;
trait EventEmitterTrait
{
    protected $listeners = [];
    protected $onceListeners = [];
    /**
     * @param callable $listener
     */
    public function on($event, $listener)
    {
        if ($event === null) {
            throw new \InvalidArgumentException('event name must not be null');
        }
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        $this->listeners[$event][] = $listener;
        return $this;
    }
    /**
     * @param callable $listener
     */
    public function once($event, $listener)
    {
        if ($event === null) {
            throw new \InvalidArgumentException('event name must not be null');
        }
        if (!isset($this->onceListeners[$event])) {
            $this->onceListeners[$event] = [];
        }
        $this->onceListeners[$event][] = $listener;
        return $this;
    }
    /**
     * @param callable $listener
     */
    public function removeListener($event, $listener)
    {
        if ($event === null) {
            throw new \InvalidArgumentException('event name must not be null');
        }
        if (isset($this->listeners[$event])) {
            $index = \array_search($listener, $this->listeners[$event], \true);
            if (\false !== $index) {
                unset($this->listeners[$event][$index]);
                if (\count($this->listeners[$event]) === 0) {
                    unset($this->listeners[$event]);
                }
            }
        }
        if (isset($this->onceListeners[$event])) {
            $index = \array_search($listener, $this->onceListeners[$event], \true);
            if (\false !== $index) {
                unset($this->onceListeners[$event][$index]);
                if (\count($this->onceListeners[$event]) === 0) {
                    unset($this->onceListeners[$event]);
                }
            }
        }
    }
    public function removeAllListeners($event = null)
    {
        if ($event !== null) {
            unset($this->listeners[$event]);
        } else {
            $this->listeners = [];
        }
        if ($event !== null) {
            unset($this->onceListeners[$event]);
        } else {
            $this->onceListeners = [];
        }
    }
    public function listeners($event = null) : array
    {
        if ($event === null) {
            $events = [];
            $eventNames = \array_unique(\array_merge(\array_keys($this->listeners), \array_keys($this->onceListeners)));
            foreach ($eventNames as $eventName) {
                $events[$eventName] = \array_merge(isset($this->listeners[$eventName]) ? $this->listeners[$eventName] : [], isset($this->onceListeners[$eventName]) ? $this->onceListeners[$eventName] : []);
            }
            return $events;
        }
        return \array_merge(isset($this->listeners[$event]) ? $this->listeners[$event] : [], isset($this->onceListeners[$event]) ? $this->onceListeners[$event] : []);
    }
    /**
     * @param mixed[] $arguments
     */
    public function emit($event, $arguments = [])
    {
        if ($event === null) {
            throw new \InvalidArgumentException('event name must not be null');
        }
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                $listener(...$arguments);
            }
        }
        if (isset($this->onceListeners[$event])) {
            $listeners = $this->onceListeners[$event];
            unset($this->onceListeners[$event]);
            foreach ($listeners as $listener) {
                $listener(...$arguments);
            }
        }
    }
}
