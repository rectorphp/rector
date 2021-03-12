<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class MultipleMethodsWithNonKernel
{
    public function singles()
    {
    }

    public function unknownMethod()
    {
    }
}

?>
-----
<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class MultipleMethodsWithNonKernelEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public function singles()
    {
    }

    public function unknownMethod()
    {
    }
    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return ['single_event' => 'singles'];
    }
}

?>
