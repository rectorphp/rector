<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\ValueObjectFactory;

use Rector\NetteToSymfony\ValueObject\EventInfo;

final class EventInfosFactory
{
    /**
     * @return EventInfo[]
     */
    public function create(): array
    {
        $eventInfos = [];
        $eventInfos[] = new EventInfo(
            ['nette.application.startup', 'nette.application.request'],
            [
                'Contributte\Events\Extra\Event\Application\StartupEvent::NAME',
                'Contributte\Events\Extra\Event\Application\RequestEvent::NAME',
                'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_REQUEST',
                'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_STARTUP',
            ],
            'Symfony\Component\HttpKernel\KernelEvents',
            'REQUEST',
            'Symfony\Component\HttpKernel\Event\GetResponseEvent'
        );

        $eventInfos[] = new EventInfo(
            ['nette.application.startup', 'nette.application.request'],
            [
                'Contributte\Events\Extra\Event\Application\StartupEvent::NAME',
                'Contributte\Events\Extra\Event\Application\RequestEvent::NAME',
                'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_REQUEST',
                'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_STARTUP',
            ],
            'Symfony\Component\HttpKernel\KernelEvents',
            'REQUEST',
            'Symfony\Component\HttpKernel\Event\GetResponseEvent'
        );

        $eventInfos[] = new EventInfo(
            ['nette.application.presenter', 'nette.application.presenter.startup'],
            [
                'Contributte\Events\Extra\Event\Application\PresenterEvent::NAME',
                'Contributte\Events\Extra\Event\Application\PresenterStartupEvent::NAME',
                'Contributte\Events\Extra\Event\Application\PresenterShutdownEvent::NAME',
                'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_PRESENTER_SHUTDOWN',
                'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_PRESENTER_STARTUP',
                'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_PRESENTER',
            ],
            'Symfony\Component\HttpKernel\KernelEvents',
            'CONTROLLER',
            'Symfony\Component\HttpKernel\Event\FilterControllerEvent'
        );

        $eventInfos[] = new EventInfo(
            ['nette.application.error'],
            [
                'Contributte\Events\Extra\Event\Application\ErrorEvent::NAME',
                'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_ERROR',
            ],
            'Symfony\Component\HttpKernel\KernelEvents',
            'EXCEPTION',
            'Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent'
        );

        $eventInfos[] = new EventInfo(
            ['nette.application.response'],
            [
                'Contributte\Events\Extra\Event\Application\ResponseEvent::NAME',
                'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_RESPONSE',
            ],
            'Symfony\Component\HttpKernel\KernelEvents',
            'RESPONSE',
            'Symfony\Component\HttpKernel\Event\FilterResponseEvent'
        );

        return $eventInfos;
    }
}
