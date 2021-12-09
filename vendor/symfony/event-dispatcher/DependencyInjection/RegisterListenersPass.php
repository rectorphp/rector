<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211209\Symfony\Component\EventDispatcher\DependencyInjection;

use RectorPrefix20211209\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use RectorPrefix20211209\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20211209\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211209\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use RectorPrefix20211209\Symfony\Component\DependencyInjection\Reference;
use RectorPrefix20211209\Symfony\Component\EventDispatcher\EventDispatcher;
use RectorPrefix20211209\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use RectorPrefix20211209\Symfony\Contracts\EventDispatcher\Event;
/**
 * Compiler pass to register tagged services for an event dispatcher.
 */
class RegisterListenersPass implements \RectorPrefix20211209\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @var mixed[]
     */
    private $hotPathEvents = [];
    /**
     * @var mixed[]
     */
    private $noPreloadEvents = [];
    /**
     * @return $this
     * @param mixed[] $hotPathEvents
     */
    public function setHotPathEvents($hotPathEvents)
    {
        $this->hotPathEvents = \array_flip($hotPathEvents);
        return $this;
    }
    /**
     * @return $this
     * @param mixed[] $noPreloadEvents
     */
    public function setNoPreloadEvents($noPreloadEvents)
    {
        $this->noPreloadEvents = \array_flip($noPreloadEvents);
        return $this;
    }
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process($container)
    {
        if (!$container->hasDefinition('event_dispatcher') && !$container->hasAlias('event_dispatcher')) {
            return;
        }
        $aliases = [];
        if ($container->hasParameter('event_dispatcher.event_aliases')) {
            $aliases = $container->getParameter('event_dispatcher.event_aliases');
        }
        $globalDispatcherDefinition = $container->findDefinition('event_dispatcher');
        foreach ($container->findTaggedServiceIds('kernel.event_listener', \true) as $id => $events) {
            $noPreload = 0;
            foreach ($events as $event) {
                $priority = $event['priority'] ?? 0;
                if (!isset($event['event'])) {
                    if ($container->getDefinition($id)->hasTag('kernel.event_subscriber')) {
                        continue;
                    }
                    $event['method'] = $event['method'] ?? '__invoke';
                    $event['event'] = $this->getEventFromTypeDeclaration($container, $id, $event['method']);
                }
                $event['event'] = $aliases[$event['event']] ?? $event['event'];
                if (!isset($event['method'])) {
                    $event['method'] = 'on' . \preg_replace_callback(['/(?<=\\b|_)[a-z]/i', '/[^a-z0-9]/i'], function ($matches) {
                        return \strtoupper($matches[0]);
                    }, $event['event']);
                    $event['method'] = \preg_replace('/[^a-z0-9]/i', '', $event['method']);
                    if (null !== ($class = $container->getDefinition($id)->getClass()) && ($r = $container->getReflectionClass($class, \false)) && !$r->hasMethod($event['method']) && $r->hasMethod('__invoke')) {
                        $event['method'] = '__invoke';
                    }
                }
                $dispatcherDefinition = $globalDispatcherDefinition;
                if (isset($event['dispatcher'])) {
                    $dispatcherDefinition = $container->getDefinition($event['dispatcher']);
                }
                $dispatcherDefinition->addMethodCall('addListener', [$event['event'], [new \RectorPrefix20211209\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \RectorPrefix20211209\Symfony\Component\DependencyInjection\Reference($id)), $event['method']], $priority]);
                if (isset($this->hotPathEvents[$event['event']])) {
                    $container->getDefinition($id)->addTag('container.hot_path');
                } elseif (isset($this->noPreloadEvents[$event['event']])) {
                    ++$noPreload;
                }
            }
            if ($noPreload && \count($events) === $noPreload) {
                $container->getDefinition($id)->addTag('container.no_preload');
            }
        }
        $extractingDispatcher = new \RectorPrefix20211209\Symfony\Component\EventDispatcher\DependencyInjection\ExtractingEventDispatcher();
        foreach ($container->findTaggedServiceIds('kernel.event_subscriber', \true) as $id => $tags) {
            $def = $container->getDefinition($id);
            // We must assume that the class value has been correctly filled, even if the service is created by a factory
            $class = $def->getClass();
            if (!($r = $container->getReflectionClass($class))) {
                throw new \RectorPrefix20211209\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }
            if (!$r->isSubclassOf(\RectorPrefix20211209\Symfony\Component\EventDispatcher\EventSubscriberInterface::class)) {
                throw new \RectorPrefix20211209\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Service "%s" must implement interface "%s".', $id, \RectorPrefix20211209\Symfony\Component\EventDispatcher\EventSubscriberInterface::class));
            }
            $class = $r->name;
            $dispatcherDefinitions = [];
            foreach ($tags as $attributes) {
                if (!isset($attributes['dispatcher']) || isset($dispatcherDefinitions[$attributes['dispatcher']])) {
                    continue;
                }
                $dispatcherDefinitions[$attributes['dispatcher']] = $container->getDefinition($attributes['dispatcher']);
            }
            if (!$dispatcherDefinitions) {
                $dispatcherDefinitions = [$globalDispatcherDefinition];
            }
            $noPreload = 0;
            \RectorPrefix20211209\Symfony\Component\EventDispatcher\DependencyInjection\ExtractingEventDispatcher::$aliases = $aliases;
            \RectorPrefix20211209\Symfony\Component\EventDispatcher\DependencyInjection\ExtractingEventDispatcher::$subscriber = $class;
            $extractingDispatcher->addSubscriber($extractingDispatcher);
            foreach ($extractingDispatcher->listeners as $args) {
                $args[1] = [new \RectorPrefix20211209\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \RectorPrefix20211209\Symfony\Component\DependencyInjection\Reference($id)), $args[1]];
                foreach ($dispatcherDefinitions as $dispatcherDefinition) {
                    $dispatcherDefinition->addMethodCall('addListener', $args);
                }
                if (isset($this->hotPathEvents[$args[0]])) {
                    $container->getDefinition($id)->addTag('container.hot_path');
                } elseif (isset($this->noPreloadEvents[$args[0]])) {
                    ++$noPreload;
                }
            }
            if ($noPreload && \count($extractingDispatcher->listeners) === $noPreload) {
                $container->getDefinition($id)->addTag('container.no_preload');
            }
            $extractingDispatcher->listeners = [];
            \RectorPrefix20211209\Symfony\Component\EventDispatcher\DependencyInjection\ExtractingEventDispatcher::$aliases = [];
        }
    }
    private function getEventFromTypeDeclaration(\RectorPrefix20211209\Symfony\Component\DependencyInjection\ContainerBuilder $container, string $id, string $method) : string
    {
        if (null === ($class = $container->getDefinition($id)->getClass()) || !($r = $container->getReflectionClass($class, \false)) || !$r->hasMethod($method) || 1 > ($m = $r->getMethod($method))->getNumberOfParameters() || !($type = $m->getParameters()[0]->getType()) instanceof \ReflectionNamedType || $type->isBuiltin() || \RectorPrefix20211209\Symfony\Contracts\EventDispatcher\Event::class === ($name = $type->getName())) {
            throw new \RectorPrefix20211209\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Service "%s" must define the "event" attribute on "kernel.event_listener" tags.', $id));
        }
        return $name;
    }
}
/**
 * @internal
 */
class ExtractingEventDispatcher extends \RectorPrefix20211209\Symfony\Component\EventDispatcher\EventDispatcher implements \RectorPrefix20211209\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    /**
     * @var mixed[]
     */
    public $listeners = [];
    /**
     * @var mixed[]
     */
    public static $aliases = [];
    /**
     * @var string
     */
    public static $subscriber;
    /**
     * @param mixed[]|callable $listener
     * @param string $eventName
     * @param int $priority
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->listeners[] = [$eventName, $listener[1], $priority];
    }
    public static function getSubscribedEvents() : array
    {
        $events = [];
        foreach ([self::$subscriber, 'getSubscribedEvents']() as $eventName => $params) {
            $events[self::$aliases[$eventName] ?? $eventName] = $params;
        }
        return $events;
    }
}
