<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ParentClassWithoutRequired;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

class SkipRequired extends ParentClassWithoutRequired
{
    #[Required]
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        parent::setEventDispatcher($eventDispatcher);
    }
}
