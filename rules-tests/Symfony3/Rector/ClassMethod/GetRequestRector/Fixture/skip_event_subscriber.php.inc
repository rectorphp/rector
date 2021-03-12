<?php declare (strict_types=1);

namespace Rector\Tests\Symfony3\Rector\ClassMethod\GetRequestRector\Fixture;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

final class SkipEventSubscriber
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
    }
}
