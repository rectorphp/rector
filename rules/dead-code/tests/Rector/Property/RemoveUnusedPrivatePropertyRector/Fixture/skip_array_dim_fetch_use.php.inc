<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipArrayDimFetchUse
{
    /**
    * @var string
    */
    private $currentProvider;

    /**
    * @var mixed[]
    */
    private $events = [];

    public function run()
    {
        if (isset($this->events[$this->currentProvider]) === false) {
            $this->events[$this->currentProvider] = 1000;
        }
    }
}
