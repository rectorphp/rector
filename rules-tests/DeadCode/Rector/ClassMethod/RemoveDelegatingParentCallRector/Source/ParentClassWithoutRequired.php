<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ParentClassWithoutRequired
{
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
    }
}
