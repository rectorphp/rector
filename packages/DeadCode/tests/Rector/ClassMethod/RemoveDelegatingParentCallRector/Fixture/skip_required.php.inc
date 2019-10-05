<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SkipRequired extends ParentClassWithoutRequired
{
    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        parent::setEventDispatcher($eventDispatcher);
    }
}

class ParentClassWithoutRequired
{
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
    }
}
