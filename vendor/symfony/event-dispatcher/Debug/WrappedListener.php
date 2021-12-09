<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211209\Symfony\Component\EventDispatcher\Debug;

use RectorPrefix20211209\Psr\EventDispatcher\StoppableEventInterface;
use RectorPrefix20211209\Symfony\Component\EventDispatcher\EventDispatcherInterface;
use RectorPrefix20211209\Symfony\Component\Stopwatch\Stopwatch;
use RectorPrefix20211209\Symfony\Component\VarDumper\Caster\ClassStub;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class WrappedListener
{
    /**
     * @var mixed[]|object|string
     */
    private $listener;
    /**
     * @var \Closure|null
     */
    private $optimizedListener;
    /**
     * @var string
     */
    private $name;
    /**
     * @var bool
     */
    private $called = \false;
    /**
     * @var bool
     */
    private $stoppedPropagation = \false;
    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|null
     */
    private $dispatcher;
    /**
     * @var string
     */
    private $pretty;
    /**
     * @var string|\Symfony\Component\VarDumper\Caster\ClassStub
     */
    private $stub;
    /**
     * @var int|null
     */
    private $priority;
    /**
     * @var bool
     */
    private static $hasClassStub;
    /**
     * @param mixed[]|callable $listener
     */
    public function __construct($listener, ?string $name, \RectorPrefix20211209\Symfony\Component\Stopwatch\Stopwatch $stopwatch, \RectorPrefix20211209\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher = null)
    {
        $this->listener = $listener;
        $this->optimizedListener = $listener instanceof \Closure ? $listener : (\is_callable($listener) ? \Closure::fromCallable($listener) : null);
        $this->stopwatch = $stopwatch;
        $this->dispatcher = $dispatcher;
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
        self::$hasClassStub = self::$hasClassStub ?? \class_exists(\RectorPrefix20211209\Symfony\Component\VarDumper\Caster\ClassStub::class);
    }
    /**
     * @return mixed[]|callable
     */
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
        $this->stub = $this->stub ?? (self::$hasClassStub ? new \RectorPrefix20211209\Symfony\Component\VarDumper\Caster\ClassStub($this->pretty . '()', $this->listener) : $this->pretty . '()');
        return ['event' => $eventName, 'priority' => null !== $this->priority ? $this->priority : (null !== $this->dispatcher ? $this->dispatcher->getListenerPriority($eventName, $this->listener) : null), 'pretty' => $this->pretty, 'stub' => $this->stub];
    }
    /**
     * @param object $event
     */
    public function __invoke($event, string $eventName, \RectorPrefix20211209\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher) : void
    {
        $dispatcher = $this->dispatcher ?: $dispatcher;
        $this->called = \true;
        $this->priority = $dispatcher->getListenerPriority($eventName, $this->listener);
        $e = $this->stopwatch->start($this->name, 'event_listener');
        ($this->optimizedListener ?? $this->listener)($event, $eventName, $dispatcher);
        if ($e->isStarted()) {
            $e->stop();
        }
        if ($event instanceof \RectorPrefix20211209\Psr\EventDispatcher\StoppableEventInterface && $event->isPropagationStopped()) {
            $this->stoppedPropagation = \true;
        }
    }
}
