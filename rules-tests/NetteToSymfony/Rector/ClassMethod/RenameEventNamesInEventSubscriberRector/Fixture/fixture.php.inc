<?php

namespace Rector\Tests\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Contributte\Events\Extra\Event\Application\PresenterStartupEvent;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'nette.application.startup' => 'someMethod',
            \Contributte\Events\Extra\Event\Application\StartupEvent::NAME => 'someMethod',
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Contributte\Events\Extra\Event\Application\PresenterStartupEvent;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            \Symfony\Component\HttpKernel\KernelEvents::REQUEST => 'someMethod',
            \Symfony\Component\HttpKernel\KernelEvents::REQUEST => 'someMethod',
        ];
    }
}

?>
