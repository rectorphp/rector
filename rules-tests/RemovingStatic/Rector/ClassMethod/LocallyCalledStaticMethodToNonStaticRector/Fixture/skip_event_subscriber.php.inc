<?php

namespace Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SkipEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['one' => 'time'];
    }
}
