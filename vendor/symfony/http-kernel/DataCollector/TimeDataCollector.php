<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\DataCollector;

use RectorPrefix20211020\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Response;
use RectorPrefix20211020\Symfony\Component\HttpKernel\KernelInterface;
use RectorPrefix20211020\Symfony\Component\Stopwatch\Stopwatch;
use RectorPrefix20211020\Symfony\Component\Stopwatch\StopwatchEvent;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @final
 */
class TimeDataCollector extends \RectorPrefix20211020\Symfony\Component\HttpKernel\DataCollector\DataCollector implements \RectorPrefix20211020\Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface
{
    protected $kernel;
    protected $stopwatch;
    public function __construct(\RectorPrefix20211020\Symfony\Component\HttpKernel\KernelInterface $kernel = null, \RectorPrefix20211020\Symfony\Component\Stopwatch\Stopwatch $stopwatch = null)
    {
        $this->kernel = $kernel;
        $this->stopwatch = $stopwatch;
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param \Throwable|null $exception
     */
    public function collect($request, $response, $exception = null)
    {
        if (null !== $this->kernel) {
            $startTime = $this->kernel->getStartTime();
        } else {
            $startTime = $request->server->get('REQUEST_TIME_FLOAT');
        }
        $this->data = ['token' => $request->attributes->get('_stopwatch_token'), 'start_time' => $startTime * 1000, 'events' => [], 'stopwatch_installed' => \class_exists(\RectorPrefix20211020\Symfony\Component\Stopwatch\Stopwatch::class, \false)];
    }
    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [];
        if (null !== $this->stopwatch) {
            $this->stopwatch->reset();
        }
    }
    /**
     * {@inheritdoc}
     */
    public function lateCollect()
    {
        if (null !== $this->stopwatch && isset($this->data['token'])) {
            $this->setEvents($this->stopwatch->getSectionEvents($this->data['token']));
        }
        unset($this->data['token']);
    }
    /**
     * Sets the request events.
     *
     * @param StopwatchEvent[] $events The request events
     */
    public function setEvents($events)
    {
        foreach ($events as $event) {
            $event->ensureStopped();
        }
        $this->data['events'] = $events;
    }
    /**
     * Gets the request events.
     *
     * @return StopwatchEvent[] The request events
     */
    public function getEvents()
    {
        return $this->data['events'];
    }
    /**
     * Gets the request elapsed time.
     *
     * @return float The elapsed time
     */
    public function getDuration()
    {
        if (!isset($this->data['events']['__section__'])) {
            return 0;
        }
        $lastEvent = $this->data['events']['__section__'];
        return $lastEvent->getOrigin() + $lastEvent->getDuration() - $this->getStartTime();
    }
    /**
     * Gets the initialization time.
     *
     * This is the time spent until the beginning of the request handling.
     *
     * @return float The elapsed time
     */
    public function getInitTime()
    {
        if (!isset($this->data['events']['__section__'])) {
            return 0;
        }
        return $this->data['events']['__section__']->getOrigin() - $this->getStartTime();
    }
    /**
     * Gets the request time.
     *
     * @return float
     */
    public function getStartTime()
    {
        return $this->data['start_time'];
    }
    /**
     * @return bool whether or not the stopwatch component is installed
     */
    public function isStopwatchInstalled()
    {
        return $this->data['stopwatch_installed'];
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'time';
    }
}
