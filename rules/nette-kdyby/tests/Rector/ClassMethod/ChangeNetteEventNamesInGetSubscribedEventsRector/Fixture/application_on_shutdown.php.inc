<?php

namespace Rector\NetteKdyby\Tests\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector\Fixture;

use Kdyby\Events\Subscriber;
use Nette\Application\Application;

class GetApplesSubscriber implements Subscriber
{
    public function getSubscribedEvents()
    {
        return [
            Application::class . '::onShutdown',
        ];
    }

    public function onShutdown(Application $application): void
    {
        $presenter = $application->getPresenter();
    }
}

?>
-----
<?php

namespace Rector\NetteKdyby\Tests\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector\Fixture;

use Kdyby\Events\Subscriber;
use Nette\Application\Application;

class GetApplesSubscriber implements Subscriber
{
    public static function getSubscribedEvents()
    {
        return [
            \Contributte\Events\Extra\Event\Application\ShutdownEvent::class => 'onShutdown',
        ];
    }

    public function onShutdown(\Contributte\Events\Extra\Event\Application\ShutdownEvent $shutdownEvent): void
    {
        $application = $shutdownEvent->getApplication();
        $presenter = $application->getPresenter();
    }
}

?>
