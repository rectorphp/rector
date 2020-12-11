<?php declare (strict_types=1);

namespace Rector\Symfony3\Tests\Rector\ClassMethod\GetRequestRector\Fixture;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

final class SkipEventSubscriber
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
    }
}
