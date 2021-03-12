<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Rector\Tests\Symfony4\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source\CustomEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

final class SkipAlreadyStringEventConstant
{
    public function run(EventDispatcher $eventDispatcher)
    {
        /** @var Event|CustomEvent $customEvent */
        $customEvent = new CustomEvent();
        $eventDispatcher->dispatch($customEvent, CustomEvent::NAME);
    }
}
