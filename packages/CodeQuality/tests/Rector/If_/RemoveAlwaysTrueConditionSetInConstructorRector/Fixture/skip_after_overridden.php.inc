<?php

namespace Rector\CodeQuality\Tests\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;

final class SkipAfterOverriden
{
    /**
     * @var bool
     */
    private $areListenerClassesLoaded = false;

    public function __construct()
    {
    }

    public function getListenerClassesToEventsToMethods(): array
    {
        if ($this->areListenerClassesLoaded) {
            return $this->listenerClassesToEvents;
        }

        $this->areListenerClassesLoaded = true;
    }
}
