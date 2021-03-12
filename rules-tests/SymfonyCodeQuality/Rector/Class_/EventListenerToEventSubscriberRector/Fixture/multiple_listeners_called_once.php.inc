<?php

namespace Rector\Tests\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class MultipleListenersCalledOnce
{
    public function singles()
    {
    }

    public function second()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class MultipleListenersCalledOnceEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public function singles()
    {
    }

    public function second()
    {
    }
    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return ['single_event' => 'singles', 'single_second_event' => 'second'];
    }
}

?>
