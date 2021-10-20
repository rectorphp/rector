<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\EventDispatcher\Debug;

use RectorPrefix20211020\Psr\EventDispatcher\StoppableEventInterface;
use RectorPrefix20211020\Symfony\Component\EventDispatcher\EventDispatcherInterface;
use RectorPrefix20211020\Symfony\Component\Stopwatch\Stopwatch;
use RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ClassStub;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class WrappedListener
{
    private $listener;
    private $optimizedListener;
    private $name;
    private $called;
    private $stoppedPropagation;
    private $stopwatch;
    private $dispatcher;
    private $pretty;
    private $stub;
    private $priority;
    private static $hasClassStub;
    public function __construct($listener, ?string $name, \RectorPrefix20211020\Symfony\Component\Stopwatch\Stopwatch $stopwatch, \RectorPrefix20211020\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher = null)
    {
        $this->listener = $listener;
        $this->optimizedListener = $listener instanceof \Closure ? $listener : (\is_callable($listener) ? \Closure::fromCallable($listener) : null);
        $this->stopwatch = $stopwatch;
        $this->dispatcher = $dispatcher;
        $this->called = \false;
        $this->stoppedPropagation = \false;
        if (\is_array($listener)) {
            $this->name = \is_object($listener[0]) ? \get_debug_type($listener[0]) : $listener[0];
            $this->pretty = $this->name . '::' . $listener[1];
        } elseif ($listener instanceof \Closure) {
            $r = new \ReflectionFunction($listener);
            if (\strpos($r->name, '{closure}') !== \false) {
                $this->pretty = $this->name = 'closure';
            } elseif ($class = $r->getClosureScopeClass()) {
                $this->name = $class->name;
                $this->pretty = $this->name . '::' . $r->name;
            } else {
                $this->pretty = $this->name = $r->name;
            }
        } elseif (\is_string($listener)) {
            $this->pretty = $this->name = $listener;
        } else {
            $this->name = \get_debug_type($listener);
            $this->pretty = $this->name . '::__invoke';
        }
        if (null !== $name) {
            $this->name = $name;
        }
        if (null === self::$hasClassStub) {
            self::$hasClassStub = \class_exists(\RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ClassStub::class);
        }
    }
    public function getWrappedListener()
    {
        return $this->listener;
    }
    public function wasCalled() : bool
    {
        return $this->called;
    }
    public function stoppedPropagation() : bool
    {
        return $this->stoppedPropagation;
    }
    public function getPretty() : string
    {
        return $this->pretty;
    }
    public function getInfo(string $eventName) : array
    {
        if (null === $this->stub) {
            $this->stub = self::$hasClassStub ? new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ClassStub($this->pretty . '()', $this->listener) : $this->pretty . '()';
        }
        return ['event' => $eventName, 'priority' => null !== $this->priority ? $this->priority : (null !== $this->dispatcher ? $this->dispatcher->getListenerPriority($eventName, $this->listener) : null), 'pretty' => $this->pretty, 'stub' => $this->stub];
    }
    /**
     * @param object $event
     */
    public function __invoke($event, string $eventName, \RectorPrefix20211020\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher) : void
    {
        $dispatcher = $this->dispatcher ?: $dispatcher;
        $this->called = \true;
        $this->priority = $dispatcher->getListenerPriority($eventName, $this->listener);
        $e = $this->stopwatch->start($this->name, 'event_listener');
        ($this->optimizedListener ?? $this->listener)($event, $eventName, $dispatcher);
        if ($e->isStarted()) {
            $e->stop();
        }
        if ($event instanceof \RectorPrefix20211020\Psr\EventDispatcher\StoppableEventInterface && $event->isPropagationStopped()) {
            $this->stoppedPropagation = \true;
        }
    }
}
