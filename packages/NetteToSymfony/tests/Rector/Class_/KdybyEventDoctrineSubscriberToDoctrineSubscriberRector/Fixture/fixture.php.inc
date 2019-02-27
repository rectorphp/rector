<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\KdybyEventDoctrineSubscriberToDoctrineSubscriberRector\Fixture;

use Kdyby\Doctrine\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SomeDoctrineEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
         return [
             Events::postPersist => 'firstFunction'
         ];
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\KdybyEventDoctrineSubscriberToDoctrineSubscriberRector\Fixture;

use Kdyby\Doctrine\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SomeDoctrineEventSubscriber implements \Doctrine\Common\EventSubscriber
{
    public function getSubscribedEvents()
    {
         return [
             \Doctrine\ORM\Events::postPersist
         ];
    }
    public function postPersist(\Doctrine\ORM\Event\LifecycleEventArgs $event): void
    {
        $this->firstFunction($event);
    }
}

?>
