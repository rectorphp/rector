<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Contributte\Events\Extra\Event\Application\PresenterStartupEvent;

final class PresenterStartup implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            PresenterStartupEvent::NAME => 'someMethod',
        ];
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Contributte\Events\Extra\Event\Application\PresenterStartupEvent;

final class PresenterStartup implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            \Symfony\Component\HttpKernel\KernelEvents::CONTROLLER => 'someMethod',
        ];
    }
}

?>
