<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector\Fixture;

final class SuperadminControllerEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [\Symfony\Component\HttpKernel\KernelEvents::REQUEST => 'onKernelRequest'];
    }
    public function onKernelRequest()
    {
        // something
    }
}
