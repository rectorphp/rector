<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'nette.application.startup' => 'someMethod',
        ];
    }

    public function someMethod(OldEvent $event)
    {
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            \Symfony\Component\HttpKernel\KernelEvents::REQUEST => 'someMethod',
        ];
    }

    public function someMethod(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event)
    {
    }
}

?>
