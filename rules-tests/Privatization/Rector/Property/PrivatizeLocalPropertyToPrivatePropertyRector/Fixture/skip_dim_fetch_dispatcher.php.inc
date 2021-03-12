<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

use Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Source\EventInterface;

final class SkipDimFetchDispatcher
{
    /**
     * @var EventInterface[]
     */
    private $dispatched = [];

    public function dispatch(EventInterface $event)
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * @return EventInterface[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatched;
    }
}
