<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SkipEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['event_name' => 'methodName'];
    }

    public function methodName()
    {
    }
}
