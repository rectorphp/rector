<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector\Source;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SuperadminControllerEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest()
    {
        // something
    }
}

