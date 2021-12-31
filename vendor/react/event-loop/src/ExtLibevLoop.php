<?php

namespace RectorPrefix20211231\React\EventLoop;

use BadMethodCallException;
use RectorPrefix20211231\libev\EventLoop;
use RectorPrefix20211231\libev\IOEvent;
use RectorPrefix20211231\libev\SignalEvent;
use RectorPrefix20211231\libev\TimerEvent;
use RectorPrefix20211231\React\EventLoop\Tick\FutureTickQueue;
use RectorPrefix20211231\React\EventLoop\Timer\Timer;
use SplObjectStorage;
/**
 * [Deprecated] An `ext-libev` based event loop.
 *
 * This uses an [unofficial `libev` extension](https://github.com/m4rw3r/php-libev),
 * that provides an interface to `libev` library.
 * `libev` itself supports a number of system-specific backends (epoll, kqueue).
 *
 * This loop does only work with PHP 5.
 * An update for PHP 7 is [unlikely](https://github.com/m4rw3r/php-libev/issues/8)
 * to happen any time soon.
 *
 * @see https://github.com/m4rw3r/php-libev
 * @see https://gist.github.com/1688204
 * @deprecated 1.2.0, use [`ExtEvLoop`](#extevloop) instead.
 */
final class ExtLibevLoop implements \RectorPrefix20211231\React\EventLoop\LoopInterface
{
    private $loop;
    private $futureTickQueue;
    private $timerEvents;
    private $readEvents = array();
    private $writeEvents = array();
    private $running;
    private $signals;
    private $signalEvents = array();
    public function __construct()
    {
        if (!\class_exists('RectorPrefix20211231\\libev\\EventLoop', \false)) {
            throw new \BadMethodCallException('Cannot create ExtLibevLoop, ext-libev extension missing');
        }
        $this->loop = new \RectorPrefix20211231\libev\EventLoop();
        $this->futureTickQueue = new \RectorPrefix20211231\React\EventLoop\Tick\FutureTickQueue();
        $this->timerEvents = new \SplObjectStorage();
        $this->signals = new \RectorPrefix20211231\React\EventLoop\SignalsHandler();
    }
    public function addReadStream($stream, $listener)
    {
        if (isset($this->readEvents[(int) $stream])) {
            return;
        }
        $callback = function () use($stream, $listener) {
            \call_user_func($listener, $stream);
        };
        $event = new \RectorPrefix20211231\libev\IOEvent($callback, $stream, \RectorPrefix20211231\libev\IOEvent::READ);
        $this->loop->add($event);
        $this->readEvents[(int) $stream] = $event;
    }
    public function addWriteStream($stream, $listener)
    {
        if (isset($this->writeEvents[(int) $stream])) {
            return;
        }
        $callback = function () use($stream, $listener) {
            \call_user_func($listener, $stream);
        };
        $event = new \RectorPrefix20211231\libev\IOEvent($callback, $stream, \RectorPrefix20211231\libev\IOEvent::WRITE);
        $this->loop->add($event);
        $this->writeEvents[(int) $stream] = $event;
    }
    public function removeReadStream($stream)
    {
        $key = (int) $stream;
        if (isset($this->readEvents[$key])) {
            $this->readEvents[$key]->stop();
            $this->loop->remove($this->readEvents[$key]);
            unset($this->readEvents[$key]);
        }
    }
    public function removeWriteStream($stream)
    {
        $key = (int) $stream;
        if (isset($this->writeEvents[$key])) {
            $this->writeEvents[$key]->stop();
            $this->loop->remove($this->writeEvents[$key]);
            unset($this->writeEvents[$key]);
        }
    }
    public function addTimer($interval, $callback)
    {
        $timer = new \RectorPrefix20211231\React\EventLoop\Timer\Timer($interval, $callback, \false);
        $that = $this;
        $timers = $this->timerEvents;
        $callback = function () use($timer, $timers, $that) {
            \call_user_func($timer->getCallback(), $timer);
            if ($timers->contains($timer)) {
                $that->cancelTimer($timer);
            }
        };
        $event = new \RectorPrefix20211231\libev\TimerEvent($callback, $timer->getInterval());
        $this->timerEvents->attach($timer, $event);
        $this->loop->add($event);
        return $timer;
    }
    public function addPeriodicTimer($interval, $callback)
    {
        $timer = new \RectorPrefix20211231\React\EventLoop\Timer\Timer($interval, $callback, \true);
        $callback = function () use($timer) {
            \call_user_func($timer->getCallback(), $timer);
        };
        $event = new \RectorPrefix20211231\libev\TimerEvent($callback, $interval, $interval);
        $this->timerEvents->attach($timer, $event);
        $this->loop->add($event);
        return $timer;
    }
    public function cancelTimer(\RectorPrefix20211231\React\EventLoop\TimerInterface $timer)
    {
        if (isset($this->timerEvents[$timer])) {
            $this->loop->remove($this->timerEvents[$timer]);
            $this->timerEvents->detach($timer);
        }
    }
    public function futureTick($listener)
    {
        $this->futureTickQueue->add($listener);
    }
    public function addSignal($signal, $listener)
    {
        $this->signals->add($signal, $listener);
        if (!isset($this->signalEvents[$signal])) {
            $signals = $this->signals;
            $this->signalEvents[$signal] = new \RectorPrefix20211231\libev\SignalEvent(function () use($signals, $signal) {
                $signals->call($signal);
            }, $signal);
            $this->loop->add($this->signalEvents[$signal]);
        }
    }
    public function removeSignal($signal, $listener)
    {
        $this->signals->remove($signal, $listener);
        if (isset($this->signalEvents[$signal]) && $this->signals->count($signal) === 0) {
            $this->signalEvents[$signal]->stop();
            $this->loop->remove($this->signalEvents[$signal]);
            unset($this->signalEvents[$signal]);
        }
    }
    public function run()
    {
        $this->running = \true;
        while ($this->running) {
            $this->futureTickQueue->tick();
            $flags = \RectorPrefix20211231\libev\EventLoop::RUN_ONCE;
            if (!$this->running || !$this->futureTickQueue->isEmpty()) {
                $flags |= \RectorPrefix20211231\libev\EventLoop::RUN_NOWAIT;
            } elseif (!$this->readEvents && !$this->writeEvents && !$this->timerEvents->count() && $this->signals->isEmpty()) {
                break;
            }
            $this->loop->run($flags);
        }
    }
    public function stop()
    {
        $this->running = \false;
    }
}
