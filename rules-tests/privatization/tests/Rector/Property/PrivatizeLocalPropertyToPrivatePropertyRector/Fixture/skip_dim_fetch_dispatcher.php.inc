<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

use Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Source\EventInterface;

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
