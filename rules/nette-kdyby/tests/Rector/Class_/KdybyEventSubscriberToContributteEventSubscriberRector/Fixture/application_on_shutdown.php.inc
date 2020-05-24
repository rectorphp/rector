<?php

namespace Rector\NetteKdyby\Tests\Rector\Class_\KdybyEventSubscriberToContributteEventSubscriberRector\Fixture;

use Kdyby\Events\Subscriber;
use Nette\Application\Application;

class GetApplesSubscriber implements Subscriber
{
    public function getSubscribedEvents()
    {
        return [
            Application::class . '::onShutdown',
            CustomService::class . '::onCopy' => 'onCustomCopy',
        ];
    }

    public function onShutdown(Application $application): void
    {
        $presenter = $application->getPresenter();
    }

    public function onCustomCopy()
    {
    }
}

?>
-----
<?php

namespace Rector\NetteKdyby\Tests\Rector\Class_\KdybyEventSubscriberToContributteEventSubscriberRector\Fixture;

use Kdyby\Events\Subscriber;
use Nette\Application\Application;

class GetApplesSubscriber implements Subscriber
{
    public static function getSubscribedEvents()
    {
        return [
            \Contributte\Events\Extra\Event\Application\ShutdownEvent::class => 'onShutdown',
            CustomService::class . '::onCopy' => 'onCustomCopy',
        ];
    }

    public function onShutdown(\Contributte\Events\Extra\Event\Application\ShutdownEvent $shutdownEvent): void
    {
        $application = $shutdownEvent->getApplication();
        $presenter = $application->getPresenter();
    }

    public function onCustomCopy()
    {
    }
}

?>
