<?php declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Source;

use Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture\MultipleCallsListener;
use Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture\MultipleMethods;
use Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture\SomeListener;
use Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture\WithPriorityListener;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Kernel;

final class ListenersKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $eventDispatcherDefinition = $containerBuilder->register('event_dispatcher', EventDispatcher::class);

        $this->registerSimpleListener($containerBuilder, $eventDispatcherDefinition);
        $this->registerWithPriority($containerBuilder, $eventDispatcherDefinition);
        $this->registerMultiple($containerBuilder, $eventDispatcherDefinition);
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/_tmp';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/_tmp';
    }

    private function registerSimpleListener(ContainerBuilder $containerBuilder, Definition $eventDispatcherDefinition): void
    {
        $containerBuilder->register(SomeListener::class);

        /* @see \Symfony\Component\EventDispatcher\EventDispatcher::addListener() */
        $eventDispatcherDefinition->addMethodCall(
            'addListener',
            ['some_event', [new Reference(SomeListener::class), 'methodToBeCalled']]
        );
    }

    private function registerWithPriority(ContainerBuilder $containerBuilder, Definition $eventDispatcherDefinition): void
    {
        $containerBuilder->register(WithPriorityListener::class);

        /* @see \Symfony\Component\EventDispatcher\EventDispatcher::addListener() */
        $eventDispatcherDefinition->addMethodCall(
            'addListener',
            ['some_event', [new Reference(WithPriorityListener::class), 'callMe'], 1540]
        );
    }

    private function registerMultiple(ContainerBuilder $containerBuilder, Definition $eventDispatcherDefinition): void
    {
        $containerBuilder->register(MultipleMethods::class);

        /* @see \Symfony\Component\EventDispatcher\EventDispatcher::addListener() */
        $eventDispatcherDefinition->addMethodCall(
            'addListener',
            ['single_event', [new Reference(MultipleMethods::class), 'singles']]
        );

        $eventDispatcherDefinition->addMethodCall(
            'addListener',
            ['multi_event', [new Reference(MultipleMethods::class), 'callMe']]
        );

        $eventDispatcherDefinition->addMethodCall(
            'addListener',
            ['multi_event', [new Reference(MultipleMethods::class), 'meToo']]
        );
    }
}
